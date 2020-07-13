<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Subscription;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Validation\ValidationException;

class SubscriptionController extends Controller
{
    /**
     * Providers
     */
    private const APPLE = 'apple';

    private $activeProvider = '';

    /**
     * Endpoint which receives subscription statuses from app services
     * and sets correct platform
     * @param Request $request
     * @throws Exception
     */
    public function index(Request $request): void
    {
        if ($request->has('notification_type')) {
            $this->activeProvider = self::APPLE;
        }

        switch ($this->activeProvider) {
            case self::APPLE:
                $this->processAppleSubscription($request);
                break;
            default:
                // TODO Change this part to logging solution, for example Sentry
                // TODO To check webhook usage on not described cases
                throw new Exception('Incorrect response');
        }
    }

    private function processAppleSubscription(Request $request): void
    {
        // Validate request
        try {
            $this->isValidAppleRequest($request);
        } catch (ValidationException $e) {
            throw new $e;
        }

//        $event = $this->getAppleEvent($request->notification_type);
//        dump($event);

        // TODO mapping
        Subscription::updateOrCreate(
            ['id' => $request->auto_renew_adam_id, 'platform' => $this->activeProvider],
            [
                'product' => $request->auto_renew_product_id,
                'auto_renew_status' => $request->auto_renew_status,
                'status_change_date' => $request->auto_renew_status_change_date,
                'notification_type' => $request->notification_type,
                'request' => json_encode($request->all()),
            ]
        );
    }

    /**
     * Apple request validation
     * @param Request $request
     * @throws ValidationException
     */
    private function isValidAppleRequest(Request $request): void
    {
        $this->validate($request, [
            'auto_renew_adam_id' => 'required|string',
            'auto_renew_product_id' => 'required|string',
            'auto_renew_status' => 'required|boolean',
            'auto_renew_status_change_date' => 'required|date',
            'notification_type' => 'required|string',
        ]);
    }

//    private function getAppleEvent(string $notificationType): ?string
//    {
//        switch ($notificationType) {
//            case 'INITIAL_BUY':
//                return 'Customer completed an initial purchase of a subscription';
//                break;
//            case 'CANCEL, DID_CHANGE_RENEWAL_STATUS, INTERACTIVE_RENEWAL':
//                return 'Subscription is active; user upgraded to another SKU';
//                break;
//            case 'INTERACTIVE_RENEWAL, DID_CHANGE_RENEWAL_PREF':
//                return 'Subscription is active; user downgraded to another SKU';
//                break;
//            case 'DID_CHANGE_RENEWAL_STATUS':
//                return 'Subscription has expired; user resubscribed to the same SKU';
//                break;
//            case 'INTERACTIVE_RENEWAL, DID_CHANGE_RENEWAL_STATUS':
//                return 'Subscription has expired; user resubscribed to another SKU (upgrade or downgrade)';
//                break;
//            case 'DID_CHANGE_RENEWAL_STATUS':
//                return 'User disabled subscription auto-renewal in the App Store account\'s Subscriptions settings, effectively canceling the subscription';
//                break;
//            default
//        }
//    }
}
