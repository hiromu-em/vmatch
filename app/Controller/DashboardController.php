<?php
declare(strict_types=1);

namespace Controller;

use Core\ViewRenderer;
use Core\Request;
use Core\Session;
use Core\Response;
use Service\DashboardService;

class DashboardController
{

    public function __construct(
        private Request $request,
        private Response $response,
        private Session $session
    ) {
    }

    /**
     * ダッシュボード画面を表示する<br>
     * 画面をリロードする際は、Sessionから取得して画面に表示する
     */
    public function showDashboard(ViewRenderer $viewRenderer, DashboardService $dashboardService): never
    {
        if (!$this->session->has('vtuber_channelList')) {

            $this->session->setArray('vtuber_channelList', $dashboardService->getAllVtuberData());
        }

        $vtuberChannelList['vtuberChannels'] = $this->session->getArray('vtuber_channelList');
        $viewRenderer->render('dashboard', $vtuberChannelList);
    }

    /**
    * 選択したChannelIDがユーザーの紐付けているchannelIDと重複しているか比較する<br>
     * 重複していない場合、ユーザーIDと選択したChannelIDを紐付ける
     */
    public function assignChannelIdToUser()
    {
        $selectChannelIds = $this->request->fetchInputValue('selected_members');
        $userId = $this->session->getStr('user_id');
    }
}