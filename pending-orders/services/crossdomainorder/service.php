<?php
require_once (PLUGIN_PATH . "/sossdata/SOSSData.php");
require_once (PLUGIN_PATH . "/phpcache/cache.php");
require_once (PLUGIN_PATH . "/auth/auth.php");
class OrderService {
    
    
    public function getAllPendingOrders($req){
        
        $data =Auth::CrossDomainAPICall(MAIN_STORE_DOMAIN,"/components/raha/order-handler/service/AllPendingOrder?tname=".HOST_NAME);
        if($data->success){
            return $data->result;
        }else{
            return null;
        }
    }



    public function postApproveOrder($req){
        $body =$req->Body(true);
        $postbody->id=$body->invoiceNo;
        $postbody->order=$body;
        $postbody->status="taken";
        $data =Auth::CrossDomainAPICall(MAIN_STORE_DOMAIN,"/components/raha/order-handler/service/OrderChange","POST",$postbody);
        if($data->success){
            return $data->result;
        }else{
            return null;
        }
    }
}

?>