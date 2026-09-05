<?php

namespace  App\Http\Interfaces\Admin;
use App\Models\Shipment;


interface  ShipmentInterface{
    public function  index($request);

    public function show($id);

    public function destroy($id);

    public function edit($id);

    public function update($request, Shipment $id);
    
    public function cancelledShipments();

    public function returnCost($id);

}
