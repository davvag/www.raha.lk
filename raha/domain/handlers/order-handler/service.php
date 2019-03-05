<?php
require_once(PLUGIN_PATH . "/auth/auth.php");
class OrderService {
    public function getAllPendingOrder($req,$res){
        if(isset($_GET["tname"])){
            require_once (PLUGIN_PATH . "/sossdata/SOSSData.php");
            $result = SOSSData::Query ("orderheader_pending", urlencode("tenant:".$_GET["tname"].""));
            if($result->success){
                return $result->result;
            }else{
                $res->SetError ("Order Not Found to Approve");
                return null;
            }
        }else{
            $res->SetError ("Wrong Request.");
            return null;
        }
    }

    public function postOrderChange($req,$res){
        $data=$req->Body(true);
        $user= Auth::AutendicateDomain($req->headers()->rhost,$req->headers()->sosskey,"OrderChange","psot");
        if(isset($user->userid)){
            switch($data->status){
                case "taken":
                    return $this->orderTaken($data->id,$data->order,$req->headers()->rhost);
                    break;
                default:
                    return null;
                    break;
            }
            
        }

    }

    private function orderTaken($id,$order,$tenant){
        require_once (PLUGIN_PATH . "/sossdata/SOSSData.php");
        $result = SOSSData::Query ("orderheader_pending", urlencode("invoiceNo:".$id.""));
        if($result->success){
            if(count($result->result)!=0){
                $dbOrder=$result->result[0];
                $detailResults=SOSSData::Query ("orderdetails_pending", urlencode("invoiceNo:".$id.""));
                if($result->success){
                    $dbOrder->Item=$result->result;
                }
                
                if($tenant==$dbOrder->tenant){
                    $order->preparedByID=$dbOrder->preparedByID;
                    $order->preparedBy=$dbOrder->preparedBy;
                    $order->PaymentComplete="N";
                    $order->balance=$order->total;
                    unset($order->invoiceNo);

                    $result = SOSSData::Insert ("orderheader", $order,$tenantId = null);   
                    if($result->success){
                        $order->invoiceNo = $result->result->generatedId;
                        foreach($order->Items as $key=>$value){
                            $order->Items[$key]->invoiceNo=$order->invoiceNo;
                        }
                        $result = SOSSData::Insert ("orderdetails", $order->Item,$tenantId = null);
                        $result = SOSSData::Delete ("orderdetails_pending", $dbOrder->Item,$tenantId = null);
                        $result = SOSSData::Delete ("orderheader_pending", $dbOrder,$tenantId = null);
                        return $order;
                    }else{
                        return "Error Saving";
                    }
                }else{
                    return "Cann't Aprove this from your Tanant";
                }
            }else{
                return null;
            }
        }
        return null;
    }
}

?>