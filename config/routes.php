<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

/**
 * The default class to use for all routes
 *
 * The following route classes are supplied with CakePHP and are appropriate
 * to set as the default:
 *
 * - Route
 * - InflectedRoute
 * - DashedRoute
 *
 * If no call is made to `Router::defaultRouteClass()`, the class used is
 * `Route` (`Cake\Routing\Route\Route`)
 *
 * Note that `Route` does not do any inflections on URLs which will result in
 * inconsistently cased URLs when used with `:plugin`, `:controller` and
 * `:action` markers.
 *
 * Cache: Routes are cached to improve performance, check the RoutingMiddleware
 * constructor in your `src/Application.php` file to change this behavior.
 *
 */
Router::defaultRouteClass(DashedRoute::class);

Router::scope('/', function (RouteBuilder $routes) {
    // Register scoped middleware for in scopes.
    $routes->registerMiddleware('csrf', new CsrfProtectionMiddleware([
        'httpOnly' => true
    ]));

   
    $routes->connect('/admin', ['controller' => 'Users', 'action' => 'login']);
    $routes->connect('/terms-of-services', ['controller' => 'Pages', 'action' => 'terms-of-services']);



    $routes->connect('/payment/*', ['controller' => 'Pages', 'action' => 'payment']);

    $routes->connect('/pay/*', ['controller' => 'Pages', 'action' => 'pay']);

    $routes->connect('/cancel/*', ['controller' => 'Pages', 'action' => 'cancel']);
    $routes->connect('/success/*', ['controller' => 'Pages', 'action' => 'success']);

    /**
     * Apply a middleware to the current route scope.
     * Requires middleware to be registered via `Application::routes()` with `registerMiddleware()`
     */
    //$routes->applyMiddleware('csrf');

    /**
     * Here, we are connecting '/' (base path) to a controller called 'Pages',
     * its action called 'display', and we pass a param to select the view file
     * to use (in this case, src/Template/Pages/home.ctp)...
     */
    $routes->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);

    /**
     * ...and connect the rest of 'Pages' controller's URLs.
     */
    $routes->connect('/pages/*', ['controller' => 'Pages', 'action' => 'display']);



    /**
     * Connect catchall routes for all controllers.
     *
     * Using the argument `DashedRoute`, the `fallbacks` method is a shortcut for
     *
     * ```
     * $routes->connect('/:controller', ['action' => 'index'], ['routeClass' => 'DashedRoute']);
     * $routes->connect('/:controller/:action/*', [], ['routeClass' => 'DashedRoute']);
     * ```
     *
     * Any route class can be used with this method, such as:
     * - DashedRoute
     * - InflectedRoute
     * - Route
     * - Or your own route class
     *
     * You can remove these routes once you've connected the
     * routes you want in your application.
     */
    $routes->fallbacks(DashedRoute::class);
});

/**
 * If you need a different set of middleware or none at all,
 * open new scope and define routes there.
 *
 * ```
 * Router::scope('/api', function (RouteBuilder $routes) {
 *     // No $routes->applyMiddleware() here.
 *     // Connect API actions here.
 * });
 * ```
 */


Router::prefix('api', function ($routes) {
   // $routes->resources('Users');
    $routes->extensions(['json', 'xml']);
   // Router::connect('/ping', ['controller' => 'Users', 'action' => 'ping']);
    $routes->connect('/paypalTokenData', ['controller' => 'Users', 'action' => 'paypalTokenData']);
    $routes->connect('/savePaymentDetails', ['controller' => 'Users', 'action' => 'savePaymentDetails']);
    $routes->connect('/jwtTokenData', ['controller' => 'Users', 'action' => 'jwtTokenData']);
    $routes->connect('/ping', ['controller' => 'Users', 'action' => 'ping']);
    $routes->connect('/login', ['controller' => 'Users', 'action' => 'login']);
    $routes->connect('/register', ['controller' => 'Users', 'action' => 'register']);
    $routes->connect('/sociallogin', ['controller' => 'Users', 'action' => 'sociallogin']);
    $routes->connect('/forgotPassword', ['controller' => 'Users', 'action' => 'forgotPassword']);
    $routes->connect('/retaurantProfile', ['controller' => 'Users', 'action' => 'retaurantProfile']);
    $routes->connect('/logout', ['controller' => 'Users', 'action' => 'logout']);
    $routes->connect('/changePassword', ['controller' => 'Users', 'action' => 'changePassword']);
    $routes->connect('/userRatingList', ['controller' => 'Users', 'action' => 'userRatingList']);
    $routes->connect('/addToFavourite', ['controller' => 'Users', 'action' => 'addToFavourite']);
    $routes->connect('/favouriteList', ['controller' => 'Users', 'action' => 'favouriteList']);
    $routes->connect('/deleteFavRestaurant', ['controller' => 'Users', 'action' => 'deleteFavRestaurant']);
    $routes->connect('/getRatings', ['controller' => 'Users', 'action' => 'getRatings']);
    $routes->connect('/getReviewsQuesAns', ['controller' => 'Users', 'action' => 'getReviewsQuesAns']);
    $routes->connect('/submitReviews', ['controller' => 'Users', 'action' => 'submitReviews']);
    $routes->connect('/restRatingList', ['controller' => 'Users', 'action' => 'restRatingList']);
    $routes->connect('/viewResaturantProfile', ['controller' => 'Users', 'action' => 'viewResaturantProfile']);
    $routes->connect('/getRestaurantList', ['controller' => 'Users', 'action' => 'getRestaurantList']);
    $routes->connect('/getTopRestaurantList', ['controller' => 'Users', 'action' => 'getTopRestaurantList']);
    $routes->connect('/getViewRestaurant', ['controller' => 'Users', 'action' => 'getViewRestaurant']);
    $routes->connect('/userRestRating', ['controller' => 'Users', 'action' => 'userRestRating']);
    $routes->connect('/getRestwithMenu', ['controller' => 'Users', 'action' => 'getRestwithMenu']);
    $routes->connect('/viewCustomerProfile', ['controller' => 'Users', 'action' => 'viewCustomerProfile']);
    $routes->connect('/editCustomerProfile', ['controller' => 'Users', 'action' => 'editCustomerProfile']);
    $routes->connect('/searchRestaurant', ['controller' => 'Users', 'action' => 'searchRestaurant']);
	$routes->connect('/getReviewsPercentage', ['controller' => 'Users', 'action' => 'getReviewsPercentage']);

    //OrdersControler
    $routes->connect('/neworders', ['controller' => 'Orders', 'action' => 'neworders']);
    $routes->connect('/orderAcceptReject', ['controller' => 'Orders', 'action' => 'orderAcceptReject']);
    $routes->connect('/acceptedorders', ['controller' => 'Orders', 'action' => 'acceptedorders']);
    $routes->connect('/pastorders', ['controller' => 'Orders', 'action' => 'pastorders']);
    $routes->connect('/placeOrder', ['controller' => 'Orders', 'action' => 'placeOrder']);
    $routes->connect('/upcomingorders', ['controller' => 'Orders', 'action' => 'upcomingorders']);
    $routes->connect('/ordersHistory', ['controller' => 'Orders', 'action' => 'ordersHistory']);
	$routes->connect('/orderComplete', ['controller' => 'Orders', 'action' => 'orderComplete']);

    //MenuItemsControler
	$routes->connect('/getCategoryListing', ['controller' => 'MenuItems', 'action' => 'getCategoryListing']);
    $routes->connect('/addMenuItem', ['controller' => 'MenuItems', 'action' => 'addMenuItem']);
    $routes->connect('/editMenuItem', ['controller' => 'MenuItems', 'action' => 'editMenuItem']);
    $routes->connect('/getMenuItemList', ['controller' => 'MenuItems', 'action' => 'getMenuItemList']);
    $routes->connect('/viewMenuData', ['controller' => 'MenuItems', 'action' => 'viewMenuData']);
	$routes->connect('/deleteMenuItem', ['controller' => 'MenuItems', 'action' => 'deleteMenuItem']);
	$routes->connect('/addCategoryList', ['controller' => 'MenuItems', 'action' => 'addCategoryList']);
	$routes->connect('/updateCategory', ['controller' => 'MenuItems', 'action' => 'updateCategory']);
    $routes->connect('/deleteCategory', ['controller' => 'MenuItems', 'action' => 'deleteCategory']);
	  
	  //notificationsController
    $routes->connect('/notificationList', ['controller' => 'Notifications', 'action' => 'notificationList']);
    $routes->connect('/notificationRead', ['controller' => 'Notifications', 'action' => 'notificationRead']);
    $routes->fallbacks('InflectedRoute');
});