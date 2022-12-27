<?php

namespace GertTimmerman\StatamicZapier\Listeners;

use GuzzleHttp\Client;
use Statamic\Events\FormSubmitted;
use Illuminate\Support\Facades\Http;
use GertTimmerman\StatamicZapier\Webhooks;

class PushToWebhook
{
    /**
     * Handle the event.
     *
     * @param  \Statamic\Events\FormSubmitted  $event
     * @return void
     */
    public function handle(FormSubmitted $event)
    {
        $webhooks = Webhooks::byForm($event->submission->form->handle());

        if (is_null($webhooks)) return;

        foreach ($webhooks as $webhook) {
            $this->sendToWebhook($webhook['webhook'], $event);
        }
    }

    /**
     * Send data to webhook
     */
    private function sendToWebhook($webhookUrl, FormSubmitted $event)
    {
        // all data
        $data = $event->submission->data();
        $data['submission_id'] = $event->submission->id();


        // filter out the attachments
        $attachmentsFields = $event->submission->fields()->filter(function ($value, $key) use ($data) {
            return is_array($value) && isset($value['type']) && $value['type'] == "assets" && isset($data[$key]) && $data[$key] != null;
        });

        $multipartBody = [];

        foreach($attachmentsFields as $handle => $value) {
            $file = request()->file($handle);
            $multipartBody[] = [
                'name' => $handle,
                'contents' => $file->get(),
                'filename' => $file->getClientOriginalName(),
                'Content-type' => 'multipart/form-data'
            ];
            unset($data[$handle]);
        }

        foreach($data as $name => $value) {
            if (is_array($value)) {
                if (count($value) == 1) {
                    $value = $value[0];
                } else {
                    $value = (request()->input($name) == null) ? null : json_encode($value);
                }
            }
            $multipartBody[] = [
                'name' => $name,
                'contents' => $value,
                'Content-type' => 'application/json'
            ];
        }
 
        // send to webhookUrl
        $client = new Client();
        $response = $client->request('POST', $webhookUrl, [
                'multipart' => $multipartBody,
            ],
            [
                'headers' => [
                    'Accept' => 'application/json'
                ]
            ]
        );
    }
}
