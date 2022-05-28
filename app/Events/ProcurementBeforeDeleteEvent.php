<?php
namespace App\Events;

use Illuminate\Queue\SerializesModels;
use App\Models\Procurement;

class ProcurementBeforeDeleteEvent
{
    use SerializesModels;
    public $procurement;

    public function __construct( Procurement $procurement )
    {
        $this->procurement  =   $procurement;
    }
}