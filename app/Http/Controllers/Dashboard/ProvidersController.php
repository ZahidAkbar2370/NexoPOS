<?php

/**
 * NexoPOS Controller
 * @since  1.0
**/

namespace App\Http\Controllers\Dashboard;
use App\Services\Validation;
use App\Crud\ProviderCrud;
use App\Crud\ProviderProcurementsCrud;
use App\Crud\ProviderProductsCrud;
use App\Http\Controllers\DashboardController;
use App\Models\Provider;
use App\Services\Options;
use App\Services\ProviderService;

class ProvidersController extends DashboardController
{
    public function __construct( 
        ProviderService $providerService,
        Options $options,
        Validation $validation
    ) {
        parent::__construct();

        $this->options              =   $options;
        $this->providerService      =   $providerService;
        $this->validation           =   $validation;        
    }

    /**
     * Retreive the provider list
     * @return array providers
     */
    public function list()
    {
        return $this->providerService->get();
    }

    public function listProviders()
    {
        return ProviderCrud::table();
    }

    public function createProvider()
    {
        return ProviderCrud::form();
    }

    public function editProvider( Provider $provider )
    {
        return ProviderCrud::form( $provider );
    }

    public function providerProcurements( $provider_id )
    {
        return $this->providerService->procurements( $provider_id );
    }

    public function deleteProvider( $id )
    {
        return $this->providerService->delete( $id );   
    }

    /**
     * Will return the list of procurements
     * made by the provider 
     * @param Provider $provider
     * @return string 
     */
    public function listProvidersProcurements( Provider $provider )
    {
        return ProviderProcurementsCrud::table([
            'queryParams'   =>  [
                'provider_id'   =>  $provider->id
            ],
            'title'     =>  sprintf(
                __( 'Procurements by "%s"' ),
                $provider->name
            )
        ]);
    }

    /**
     * Will list all products
     * provided by that provider
     * @param Provider $provider
     * @return array
     */
    public function listProvidersProducts( Provider $provider ) 
    {
        $procurements   =   $provider
            ->procurements()
            ->get( 'id' )
            ->map( fn( $procurement ) => $procurement->id )
            ->toArray();

        return ProviderProductsCrud::table([
            'title'         =>  sprintf( __( '%s\'s Products' ), $provider->name ),
            'queryParams'   =>  [
                'procurements'  =>  $procurements
            ]
        ]);
    }
}

