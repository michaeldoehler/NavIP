<?php
/**---------------------------------------------------------------------------------------
 |  Operations Manager
 |----------------------------------------------------------------------------------------
 | Created by : Mayaka Donnicias
 | Date: 9/8/2018
 | Time: 9:47 AM
 | Description: This is the api end point for doing CRUD operations in Nav
 | ---------------------------------------------------------------------------------------
 */

namespace donnicias\nav_ip\Engine;
use donnicias\nav_ip\Core\Client;
use donnicias\nav_ip\Traits\Wrapper;

class Manager
{
    use Wrapper;

    /**
     * Manager constructor.
     */
    public function __construct()
    {
        Manager::prepareWrapper();
    }

    /**
     *  Manager destructor.
     */
    public function __destruct()
    {
        Manager::restoreWrapper();
    }

    /**
     * @param null $filters ~ Filters
     * @param $service ~ Service to be invoked
     * @param null $method ~ Method to call, for codeunit
     * @param null $size ~ Return size of the result
     * @return string
     */
    public function Read($filters, $service, $method=null, $size=null)
    {
        try {
            $client = new Client(Manager::resolveUrl($service, $method ? 'Codeunit' : 'Page'), ['trace' => 1]);
            if ($method) {
                return $client->$method($filters);
            } else {
                return $client->ReadMultiple(['filter' => $filters ?: '', 'setSize' => $size])->ReadMultiple_Result;
            }
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * @param $datum ~ Data object to be created in Nav
     * @param $service ~ Service end to invoke to create rec in Nav
     * @return string
     */
    public function Create($datum, $service)
    {
        try{
            $client = new Client(Manager::resolveUrl($service, 'Page'), ['trace' => 1]);
            return $client->Create((object)[$service => (object)$datum]);
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * @param $record ~ Record to be deleted in Nav
     * @param $service ~ Service to invoke to delete the record
     * @return string ~ return response
     */
    public function Delete($record, $service)
    {
        try{
            $client = new Client(Manager::resolveUrl($service, 'Page'), ['trace' => 1]);
            $client->Delete(['Key'=>$record->Key]);
            return "Record deleted.";
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * @param $record ~ Record to be updated. Required
     * @param $service ~ Service name to be invoked. Required
     * @return string
     */
    public function Update($record, $service)
    {
        try {
            $client = new Client(Manager::resolveUrl($service, 'Page'), ['trace' => 1]);
            return $client->Update((object)[$service => (object)$record]);
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * @param $record ~ Rec to be updated
     * @param $service ~ Service end point to invoke
     * @return string ~ Operation response
     */
    public function Sync($record, $service){
        try {
            $sync_field = config('nav.Sync_Field');
            $record->$sync_field = config('nav.Default_Sync_Value');
            return Manager::Update($record, $service);
        }catch(\Exception $e){
            return $e;
        }
    }
}
