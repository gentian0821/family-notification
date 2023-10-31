<?php

namespace App\UseCase;

use App\Domains\MakeNotifyMessage;
use App\Repositories\LineMessageApiRepository;
use Illuminate\Support\Facades\Config;

class NotifyUseCase
{
    public function __construct(private LineMessageApiRepository $lineMessageApiRepository)
    {
    }

    public function __invoke(array $params): void
    {
        if (empty($params)) {
            return;
        }

        $makeMessage = app()->make(MakeNotifyMessage::class);

        $this->lineMessageApiRepository->push(
            [
                [
                    'type' => 'text',
                    'text' => $makeMessage($params),
                ]
            ],
            Config::get('const.fayc4_send_to')
        );
    }
}
