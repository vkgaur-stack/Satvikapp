<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ---- Public -----------------------------------------------------------
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attempt');
$routes->get('donate', 'Payments::form');
$routes->post('donate/order', 'Payments::order');
$routes->post('donate/verify', 'Payments::verify');
$routes->post('razorpay/webhook', 'Payments::webhook');

// ---- Staff web app (session login) ---------------------------------------
$routes->group('', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->get('/', 'Dashboard::index');
    $routes->get('dashboard/data', 'Dashboard::data');
    $routes->post('logout', 'Auth::logout');

    $routes->get('account/password', 'Account::password');
    $routes->post('account/password', 'Account::updatePassword');

    // Generic module screens (donors, donations, beneficiaries, ...) - see Config\Catalog
    $routes->get('m/(:segment)', 'Crud::index/$1');
    $routes->get('m/(:segment)/new', 'Crud::new/$1');
    $routes->post('m/(:segment)', 'Crud::create/$1');
    $routes->get('m/(:segment)/(:num)', 'Crud::show/$1/$2');
    $routes->get('m/(:segment)/(:num)/edit', 'Crud::edit/$1/$2');
    $routes->post('m/(:segment)/(:num)', 'Crud::update/$1/$2');
    $routes->post('m/(:segment)/(:num)/delete', 'Crud::delete/$1/$2');

    $routes->get('files/(:segment)/(:num)', 'Files::download/$1/$2');
    $routes->post('files/documents/(:num)/verify', 'Files::verify/$1');

    $routes->get('receipts/(:num)', 'Receipts::show/$1');
    $routes->post('receipts/(:num)/issue', 'Receipts::issue/$1');
    $routes->post('receipts/(:num)/email', 'Receipts::email/$1');

    $routes->get('stewardship', 'Stewardship::index');
    $routes->post('stewardship/draft', 'Stewardship::draft');
    $routes->post('stewardship/send', 'Stewardship::send');

    $routes->get('reports', 'Reports::index');
    $routes->get('reports/(:segment)', 'Reports::show/$1');
    $routes->get('reports/(:segment)/export', 'Reports::export/$1');

    $routes->get('audit', 'Audit::index');
});

// ---- REST API (bearer token) -------------------------------------------------
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api'], static function (RouteCollection $routes) {
    $routes->post('auth/token', 'Auth::token');

    $routes->group('', ['filter' => 'apiauth'], static function (RouteCollection $routes) {
        $routes->get('me', 'Auth::me');
        $routes->post('auth/revoke', 'Auth::revoke');
        $routes->get('dashboard', 'Dashboard::summary');

        $routes->get('(:segment)', 'Resource::index/$1');
        $routes->get('(:segment)/(:num)', 'Resource::show/$1/$2');
        $routes->post('(:segment)', 'Resource::create/$1');
        $routes->match(['put', 'patch'], '(:segment)/(:num)', 'Resource::update/$1/$2');
        $routes->delete('(:segment)/(:num)', 'Resource::delete/$1/$2');
    });
});
