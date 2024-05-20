<?php

namespace App\UseCase;

use App\Domains\AnalyzeMessage;
use App\Repositories\LineMessageApiRepository;
use Illuminate\Support\Facades\Log;

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
        $messages = $makeMessage($params['events'][0]);

        $this->lineMessageApiRepository->reply($messages, $params["events"][0]['replyToken']);
    }
}
