$routes->group('donors', static function ($routes) {
    $routes->get('/', 'Donor::index');                    // GET /donors
    $routes->get('create', 'Donor::create');              // GET /donors/create
    $routes->post('store', 'Donor::store');               // POST /donors/store
    $routes->get('(:num)', 'Donor::show/$1');             // GET /donors/1
    $routes->get('(:num)/edit', 'Donor::edit/$1');        // GET /donors/1/edit
    $routes->post('(:num)/update', 'Donor::update/$1');   // POST /donors/1/update
    $routes->post('(:num)/delete', 'Donor::delete/$1');   // POST /donors/1/delete
});

$routes->group('beneficiaries', static function ($routes) {
    $routes->get('/', 'Beneficiary::index');                      // GET /beneficiaries
    $routes->get('create', 'Beneficiary::create');                // GET /beneficiaries/create
    $routes->post('store', 'Beneficiary::store');                 // POST /beneficiaries/store
    $routes->get('(:num)', 'Beneficiary::show/$1');               // GET /beneficiaries/1
    $routes->get('(:num)/edit', 'Beneficiary::edit/$1');          // GET /beneficiaries/1/edit
    $routes->post('(:num)/update', 'Beneficiary::update/$1');     // POST /beneficiaries/1/update
    $routes->post('(:num)/delete', 'Beneficiary::delete/$1');     // POST /beneficiaries/1/delete
});