<?php namespace Laracasts\Validation;

use Laracasts\Validation\FactoryInterface as ValidatorFactory;
use Laracasts\Validation\ValidatorInterface as ValidatorInstance;

abstract class FormValidator {

	/**
	 * @var ValidatorFactory
	 */
	protected $validator;

	/**
	 * @var ValidatorInstance
	 */
	protected $validation;

	/**
	 * @var array
	 */
	protected $messages = [];

    protected $locales;

	/**
	 * @param ValidatorFactory $validator
	 */
	function __construct(ValidatorFactory $validator)
	{
		$this->validator = $validator;
        $this->locales = \Config::get('app.locales');
	}

	/**
	 * Validate the form data
	 *
	 * @param  mixed $formData
	 * @return mixed
	 * @throws FormValidationException
	 */
	public function validate($formData)
	{
		$formData = $this->normalizeFormData($formData);

        $mergedRules = $this->mergeRules();

        $this->validation = $this->validator->make(
			$formData,
            $mergedRules,
			$this->getValidationMessages()
		);

        $this->validation->setAttributeNames(
            $this->getAttributeNames()
        );

		if ($this->validation->fails())
		{
			throw new FormValidationException('Validation failed', $this->getValidationErrors());
		}

		return true;
	}

	/**
	 * @return array
	 */
	public function getValidationRules()
	{
		return isset($this->rules) ? $this->rules : [];
	}

    public function getLocaleValidationRules() {
        return isset($this->localeRules) ? $this->localeRules : [];
    }

	/**
	 * @return mixed
	 */
	public function getValidationErrors()
	{
		return $this->validation->errors();
	}

	/**
	 * @return mixed
	 */
	public function getValidationMessages()
	{
		return $this->messages;
	}


    /**
     * @return array
     */
    public function getAttributeNames() {
        return isset($this->attributeNames) ? $this->attributeNames : [];
    }
	/**
	 * Normalize the provided data to an array.
	 *
	 * @param  mixed $formData
	 * @return array
	 */
	protected function normalizeFormData($formData)
	{
		// If an object was provided, maybe the user
		// is giving us something like a DTO.
		// In that case, we'll grab the public properties
		// off of it, and use that.
		if (is_object($formData))
		{
        	return get_object_vars($formData);
		}

		// Otherwise, we'll just stick with what they provided.
		return $formData;
	}

    /**
     * Merges locale and normal rules
     *
     * @return array
     */
    private function mergeRules ()
    {
        $rules       = $this->getValidationRules();
        $localeRules = $this->getLocaleValidationRules();

        if ( !empty( $localeRules ) )
        {
            foreach ( $this->locales as $locale )
            {
                foreach ( $localeRules as $field => $localeRule )
                {
                    $newLocaleRules[$locale . "." . $field] = $localeRule;
                }
            }
            $mergedRules = array_merge($rules, $newLocaleRules);
        } else
        {
            $mergedRules = $rules;
        }

        return $mergedRules;
    }

}
