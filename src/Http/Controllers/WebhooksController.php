<?php

namespace GertTimmerman\StatamicZapier\Http\Controllers;

use Statamic\Facades\User;
use Illuminate\Http\Request;
use GertTimmerman\StatamicZapier\Webhooks;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Arr;

class WebhooksController extends CpController
{
    public function edit()
    {
        abort_unless(User::current()->can('configure form zapier webhooks'), 403);

        $blueprint = Webhooks::blueprint();

        $fields = $blueprint
            ->fields()
            ->addValues(Webhooks::load()->all())
            ->preProcess();

        return view('statamic-zapier::index', [
            'title' => 'Zapier Webhooks',
            'action' => cp_route('statamic-zapier.index'),
            'blueprint' => $blueprint->toPublishArray(),
            'meta' => $fields->meta(),
            'values' => $fields->values()
        ]);
    }


    public function update(Request $request)
    {
        abort_unless(User::current()->can('configure form zapier webshooks'), 403);

        $blueprint = Webhooks::blueprint();

        $fields = $blueprint->fields()->addValues($request->all());

        $fields->validate();

        $values = Arr::removeNullValues($fields->process()->values()->all());

        Webhooks::load($values)->save();
    }
}
