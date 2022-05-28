<?php

namespace Tests\Feature;

use App\Models\CashFlow;
use App\Models\CustomerAccountHistory;
use App\Models\Expense;
use App\Models\Order;
use App\Models\PaymentType;
use App\Models\Procurement;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Provider;
use App\Models\RewardSystem;
use App\Models\Role;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\UnitGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Traits\WithAuthentication;

class CreateUserTest extends TestCase
{
    use WithAuthentication, WithFaker;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    private function test_create_users()
    {
        $this->attemptAuthenticate();

        return Role::get()->map( function( $role ) {
            $password   =   Hash::make( Str::random(20) );

            $response   =   $this->withSession( $this->app[ 'session' ]->all() )
                ->json( 'post', '/api/nexopos/v4/crud/ns.users', [
                    'username'  =>  $this->faker->username(),
                    'general'   =>  [
                        'email'     =>  $this->faker->email(),
                        'password'  =>  $password,
                        'password_confirm'  =>  $password,
                        'roles'     =>  [ $role->id ],
                        'active'    =>  1, // true
                    ]
                ]);
              
            
            $response->assertJsonPath( 'status', 'success' );
            $result     =   json_decode( $response->getContent() );
            return $result->entry;
        });
    }

    public function test_created_users()
    {
        $user   =   User::first();
        $this->attemptAllRoutes( $user );
    }

    private function attemptAllRoutes( $user )
    {
        /**
         * @var User $user
         */
        $user   =   User::findOrFail( $user->id );

        $paramsModelBinding     =   [
            '/\{product\}/'                                 =>  Product::class,
            '/\{provider\}/'                                =>  Provider::class,
            '/\{procurement\}/'                             =>  Procurement::class,
            '/\{expense\}/'                                 =>  Expense::class,
            '/\{category\}/'                                =>  ProductCategory::class,
            '/\{group\}/'                                   =>  UnitGroup::class,
            '/\{unit\}/'                                    =>  Unit::class,
            '/\{reward\}/'                                  =>  RewardSystem::class,
            '/\{customer\}|\{customerAccountHistory\}/' =>  function() {
                $customerAccountHistory     =   CustomerAccountHistory::first()->id;
                $customer                   =   $customerAccountHistory->customer->id;
                return compact( 'customerAccountHistory', 'customer' );
            },
            '/\{paymentType\}/'                             =>  PaymentType::class,
            '/\{user\}/'                                    =>  User::class,
            '/\{order\}/'                                   =>  Order::class,
            '/\{tax\}/'                                     =>  Tax::class,
            '/\{cashFlow\}/'                                =>  CashFlow::class,
        ];

        /**
         * Step 1: We'll attempt registering with the email
         * to cause a failure because of the email used
         */
        $routes     =   Route::getRoutes();

        foreach( $routes as $route ) {
            $uri    =   $route->uri();

            /**
             * For now we'll only test dashboard routes
             */
            if ( strstr( $uri, 'dashboard' ) && ! strstr( $uri, 'api/' ) ) {
                /**
                 * For requests that doesn't support
                 * any paremeters
                 */
                foreach( $paramsModelBinding as $expression => $binding ) {
                    if ( preg_match( $expression, $uri ) ) {
                        if ( is_array( $binding ) ) {
                            
                            /**
                             * We want to replace all argument
                             * on the uri by the matching binding collection
                             */
                            foreach( $binding as $parameter => $value ) {
                                $uri    =   preg_replace( '/\{' . $parameter . '\}/', $value, $uri );
                            }
                        } else if ( is_string( $binding ) ) {

                            /**
                             * This are URI with a single parameter
                             * that are replaced once the binding is resolved.
                             */
                            $value  =   $binding::firstOrFail()->id;
                            $uri    =   preg_replace( $expression, $value, $uri );
                        }
                    }
                }

                /**
                 * we believe all arguments are resolved
                 * if some argument remains, we won't test
                 * those routes.
                 */
                if ( preg_match( '/\{(.+)\}/', $uri ) === 0 ) {
                    $response   =   $this
                        ->actingAs( $user )
                        ->json( 'GET', $uri );

                    $status     =   $response->baseResponse->getStatusCode();
    
                    if ( $user->roles->map( fn( $role ) => $role->namespace )->first() === 'admin' ) {
                        $this->assertTrue(
                            in_array( $status, [ 201, 200, 302, 401 ]),
                            'Unsupported HTTP response :' . $status . ' uri:' . $uri . ' user role:' . $user->roles->map( fn( $role ) => $role->namespace )->join( ',' )
                        );
                    } else {
                        $this->assertTrue(
                            in_array( $status, [ 201, 200, 302, 401 ]),
                            'Unsupported HTTP response :' . $status . ' uri:' . $uri . ' user role:' . $user->roles->map( fn( $role ) => $role->namespace )->join( ',' )
                        );
                    }
                }
            }
        }
    }
}
