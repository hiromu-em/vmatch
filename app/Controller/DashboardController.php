<?php
declare(strict_types=1);

namespace Controller;

use Core\ViewRenderer;
use Service\DashboardService;

class DashboardController
{
    public function showDashboard(ViewRenderer $viewRenderer, DashboardService $dashboardService)
    {
        $vtuberChannelList['vtuberChannels'] = $dashboardService->getAllVtuberData();

        $viewRenderer->render('dashboard', $vtuberChannelList);
    }
}