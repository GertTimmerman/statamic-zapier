<?php

namespace GertTimmerman\StatamicZapier;

use Illuminate\Support\Collection;
use Statamic\Facades\Blueprint;
use Statamic\Facades\File;
use Statamic\Facades\YAML;

class Webhooks extends Collection
{
    /**
     * Load webhooks collection.
     *
     * @param array|Collection|null $items
     */
    public function __construct($items = null)
    {
        if (! is_null($items)) {
            $items = collect($items)->all();
        }

        $this->items = $items ?? $this->getDefaults();
    }

    /**
     * Load webhooks collection.
     *
     * @param array|Collection|null $items
     * @return static
     */
    public static function load($items = null)
    {
        return new static($items);
    }

    /**
     * Get augmented.
     *
     * @return array
     */
    public function augmented()
    {
        $contentValues = Blueprint::make()
            ->setContents(['fields' => Fields::new()->getConfig()])
            ->fields()
            ->addValues($this->items)
            ->augment()
            ->values();

        $defaultValues = static::blueprint()
            ->fields()
            ->addValues($this->items)
            ->augment()
            ->values();

        return $defaultValues
            ->merge($contentValues)
            ->only(array_keys($this->items))
            ->all();
    }

   /**
     * Save site defaults collection to yaml.
     */
    public function save()
    {
        File::put($this->path(), YAML::dump($this->items));
    }

    /**
     * Get site defaults from yaml.
     *
     * @return array
     */
    protected function getDefaults()
    {
        return collect(YAML::file(__DIR__.'/../content/zapier-webhooks.yaml')->parse())
            ->merge(YAML::file($this->path())->parse())
            ->all();
    }

    /**
     * Get site defaults yaml path.
     *
     * @return string
     */
    protected function path()
    {
        return base_path('content/zapier-webhooks.yaml');
    }

    /**
    * get an array of form
    */
    public static function getForms()
    {
      $forms = [];

      \Statamic\Facades\Form::all()->map(function($form) use (&$forms) {
          $forms[$form->handle] = $form->title;
      });

      return $forms;
    }

    /**
     * Get webhook by form handle
     */
    public static function byForm($formHandle)
    {
      $formsAndWebhooks = collect(Webhooks::load()->first());

      $webhooks = $formsAndWebhooks->where('form', $formHandle)->all();

      if (is_null($webhooks)) return null;

      return $webhooks;
    }

    /**
     * Get site defaults blueprint.
     *
     * @return \Statamic\Fields\Blueprint
     */
    public static function blueprint()
    {
        return Blueprint::makeFromFields([
          'webhooks' => [
            'type' => 'grid',
            'display' => 'Zapier Webhooks',
            'instruction' => 'Add Webhooks to forms',
            'add_row' => 'Add new webhook',
            'fields' => [
              [
                'handle' => 'form',
                'field' => [
                  'type' => 'select',
                  'display' => 'Form',
                  'options' => static::getForms(),
                  'required' => 'true'
                ]
              ],
              [
                'handle' => 'webhook',
                'field' => [
                  'type' => 'text',
                  'display' => 'Zapier Webhook',
                  'validate' => 'required|active_url',
                  'input_type' => 'url'
                ]
              ]              
            ]
          ]
        ]);
    }
}