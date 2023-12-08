<?php

namespace App\UseCase;

use App\Domains\AnalyzeMessage;
use App\Domains\MakeNotifyMessage;
use App\Repositories\LineMessageApiRepository;
use Illuminate\Support\Facades\Config;

class CallbackUseCase
{
    public function __construct(
        private LineMessageApiRepository $lineMessageApiRepository,
        private AnalyzeMessage $analyzeMessage
    ) {
    }

    public function __invoke(array $params): void
    {
        if (empty($params)) {
            return;
        }

        $makeMessage = app()->make(AnalyzeMessage::class);

        $this->lineMessageApiRepository->reply(
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
