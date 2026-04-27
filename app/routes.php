<?php

declare(strict_types=1);

use App\Application\Actions\Auth\LoginAction;
use App\Application\Actions\Auth\LogoutAction;
use App\Application\Actions\Auth\MeAction;
use App\Application\Actions\Auth\RefreshTokenAction;
use App\Application\Actions\Auth\RegisterAction;

use App\Application\Actions\Brand\GetMyBrandAction;
use App\Application\Actions\Brand\UpdateBrandAction;
use App\Application\Actions\Brand\ListProductsAction;
use App\Application\Actions\Brand\CreateProductAction;
use App\Application\Actions\Brand\UpdateProductAction;
use App\Application\Actions\Brand\DeleteProductAction;

use App\Application\Actions\Influencer\ListInfluencersAction;
use App\Application\Actions\Influencer\ViewInfluencerAction;
use App\Application\Actions\Influencer\GetInfluencerPostsAction;
use App\Application\Actions\Influencer\GetMyInfluencerAction;
use App\Application\Actions\Influencer\UpdateMyInfluencerAction;
use App\Application\Actions\Influencer\AddSocialAccountAction;
use App\Application\Actions\Influencer\DeleteSocialAccountAction;

use App\Application\Actions\Category\ListCategoriesAction;

use App\Application\Actions\Affiliate\GetMyAffiliateAction;
use App\Application\Actions\Affiliate\UpdateAffiliateAction;
use App\Application\Actions\Affiliate\ListAffiliateInfluencersAction;
use App\Application\Actions\Affiliate\CreateAffiliateInfluencerAction;
use App\Application\Actions\Affiliate\UpdateAffiliateInfluencerAction;
use App\Application\Actions\Affiliate\GetEarningsAction;
use App\Application\Actions\Affiliate\GetEarningsHistoryAction;

use App\Application\Actions\Campaign\CreateCampaignAction;
use App\Application\Actions\Campaign\ListMyCampaignsAction;
use App\Application\Actions\Campaign\ViewCampaignAction;
use App\Application\Actions\Campaign\UpdateCampaignAction;
use App\Application\Actions\Campaign\DeleteCampaignAction;
use App\Application\Actions\Campaign\HireInfluencersAction;
use App\Application\Actions\Campaign\ListMyDealsAction;
use App\Application\Actions\Campaign\ViewDealAction;
use App\Application\Actions\Campaign\UpdateDealStatusAction;
use App\Application\Actions\Campaign\SubmitDeliverableAction;

use App\Application\Actions\Payment\InitiatePaymentAction;
use App\Application\Actions\Payment\ViewPaymentAction;
use App\Application\Actions\Review\CreateReviewAction;
use App\Application\Actions\Review\GetInfluencerReviewsAction;
use App\Application\Actions\Review\GetBrandReviewsAction;
use App\Application\Actions\Notification\ListNotificationsAction;
use App\Application\Actions\Notification\MarkReadAction;
use App\Application\Actions\Notification\MarkAllReadAction;
use App\Application\Actions\Admin\DashboardAction;
use App\Application\Actions\Admin\ListUsersAction;
use App\Application\Actions\Admin\UpdateUserStatusAction;
use App\Application\Actions\Admin\GetSettingsAction;
use App\Application\Actions\Admin\UpdateSettingsAction;
use App\Application\Actions\Admin\ListAllPaymentsAction;
use App\Application\Actions\Admin\MarkPayoutPaidAction;

use App\Application\Middleware\JwtAuthMiddleware;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\App;
use Slim\Exception\HttpForbiddenException;
use Slim\Routing\RouteCollectorProxy as Group;

function requireRole(string ...$roles): Closure
{
    return function (Request $request, Handler $handler) use ($roles) {
        $user = $request->getAttribute('user');
        if (!$user || !in_array($user->getRole(), $roles, true)) {
            throw new HttpForbiddenException($request, 'Access denied for your role');
        }
        return $handler->handle($request);
    };
}

return function (App $app) {
    $app->options('/{routes:.*}', function ($request, $response) {
        return $response;
    });

    $app->group('/api/v1', function (Group $group) {

        // Health check
        $group->get('/health', function ($request, $response) {
            $response->getBody()->write(json_encode(['status' => 'ok']));
            return $response->withHeader('Content-Type', 'application/json');
        });

        // =====================
        // PUBLIC ROUTES
        // =====================
        $group->post('/auth/register', RegisterAction::class);
        $group->post('/auth/login',    LoginAction::class);
        $group->post('/auth/refresh',  RefreshTokenAction::class);

        // Public influencer discovery
        // Use regex constraint {id:[0-9]+} so "me" does NOT match
        $group->get('/influencers',                 ListInfluencersAction::class);
        $group->get('/influencers/{id:[0-9]+}',     ViewInfluencerAction::class);
        $group->get('/influencers/{id:[0-9]+}/posts', GetInfluencerPostsAction::class);

        // Public categories
        $group->get('/categories', ListCategoriesAction::class);

        // Public reviews
        $group->get('/influencers/{id:[0-9]+}/reviews', GetInfluencerReviewsAction::class);
        $group->get('/brands/{id:[0-9]+}/reviews',      GetBrandReviewsAction::class);

        // =====================
        // AUTHENTICATED ROUTES
        // =====================
        $group->group('', function (Group $auth) {
            $auth->get('/me',           MeAction::class);
            $auth->post('/auth/logout', LogoutAction::class);

            // Brand routes (brand role only)
            $auth->group('/brands', function (Group $brand) {
                $brand->get('/me',                     GetMyBrandAction::class);
                $brand->patch('/me',                   UpdateBrandAction::class);
                $brand->get('/me/products',            ListProductsAction::class);
                $brand->post('/me/products',           CreateProductAction::class);
                $brand->patch('/me/products/{id}',     UpdateProductAction::class);
                $brand->delete('/me/products/{id}',    DeleteProductAction::class);
            })->add(requireRole('brand'));

            // Influencer self-management (influencer role only)
            $auth->group('/influencers/me', function (Group $inf) {
                $inf->get('',                          GetMyInfluencerAction::class);
                $inf->patch('',                        UpdateMyInfluencerAction::class);
                $inf->post('/social-accounts',         AddSocialAccountAction::class);
                $inf->delete('/social-accounts/{id}',  DeleteSocialAccountAction::class);
            })->add(requireRole('influencer'));

            // Affiliate routes (affiliate role only)
            $auth->group('/affiliates/me', function (Group $aff) {
                $aff->get('',                      GetMyAffiliateAction::class);
                $aff->patch('',                    UpdateAffiliateAction::class);
                $aff->get('/influencers',          ListAffiliateInfluencersAction::class);
                $aff->post('/influencers',         CreateAffiliateInfluencerAction::class);
                $aff->patch('/influencers/{id}',   UpdateAffiliateInfluencerAction::class);
                $aff->get('/earnings',             GetEarningsAction::class);
                $aff->get('/earnings/history',     GetEarningsHistoryAction::class);
            })->add(requireRole('affiliate'));

            // Campaign routes (brand only for management)
            $auth->group('/campaigns', function (Group $camp) {
                $camp->post('',                       CreateCampaignAction::class);
                $camp->get('/me',                     ListMyCampaignsAction::class);
                $camp->get('/{id:[0-9]+}',            ViewCampaignAction::class);
                $camp->patch('/{id:[0-9]+}',          UpdateCampaignAction::class);
                $camp->delete('/{id:[0-9]+}',         DeleteCampaignAction::class);
                $camp->post('/{id:[0-9]+}/deals',     HireInfluencersAction::class);
            })->add(requireRole('brand'));

            // Deal routes (all authenticated users — role checked inside actions)
            $auth->get('/deals/me',                   ListMyDealsAction::class);
            $auth->get('/deals/{id:[0-9]+}',          ViewDealAction::class);
            $auth->patch('/deals/{id:[0-9]+}/{action}', UpdateDealStatusAction::class);
            $auth->post('/deals/{id:[0-9]+}/deliverables', SubmitDeliverableAction::class);

            // Payment routes (brand only)
            $auth->post('/deals/{id:[0-9]+}/pay',       InitiatePaymentAction::class)->add(requireRole('brand'));
            $auth->get('/payments/{id:[0-9]+}',         ViewPaymentAction::class);

            // Review routes (any authenticated user)
            $auth->post('/deals/{id:[0-9]+}/reviews',   CreateReviewAction::class);

            // Notification routes
            $auth->get('/notifications',                ListNotificationsAction::class);
            $auth->patch('/notifications/{id:[0-9]+}/read', MarkReadAction::class);
            $auth->patch('/notifications/read-all',     MarkAllReadAction::class);

            // Admin routes
            $auth->group('/admin', function (Group $admin) {
                $admin->get('/dashboard',               DashboardAction::class);
                $admin->get('/users',                   ListUsersAction::class);
                $admin->patch('/users/{id:[0-9]+}/status', UpdateUserStatusAction::class);
                $admin->get('/settings',                GetSettingsAction::class);
                $admin->patch('/settings',              UpdateSettingsAction::class);
                $admin->get('/payments',                ListAllPaymentsAction::class);
                $admin->post('/payouts/{id:[0-9]+}/mark-paid', MarkPayoutPaidAction::class);
            })->add(requireRole('admin'));
        })->add(JwtAuthMiddleware::class);
    });
};
