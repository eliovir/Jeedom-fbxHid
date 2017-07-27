<?php
class rudpConnection{
	private $testdescriptor= "10010000bd990000000000020000000054c3a96cc3a9636f6d6d616e6465204672656554c3a96c65630000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000001000070005000c0000000c00000050c0901a10185019501751019002a8c021500268c0280c005010906a1018502050795017508150026ff00050719002aff0080c005010a8000a1018503750195041a81002a84008102750195048101c00000000000000000";
	private $testhandshake = "02010000da59000000000000";
	private $FOILS_COMMANDS = array( 
		0 => 'FOILS_HID_DEVICE_NEW', # client to server
		1 => 'FOILS_HID_DEVICE_DROPPED', # client to server
		2 => 'FOILS_HID_DEVICE_CREATED', # server to client
		3 => 'FOILS_HID_DEVICE_CLOSE', # server to client
		4 => 'FOILS_HID_FEATURE', # bidir
		5 => 'FOILS_HID_DATA', # bidir
		6 => 'FOILS_HID_GRAB', # server to client
		7 => 'FOILS_HID_RELEASE', # server to client
		8 => 'FOILS_HID_FEATURE_SOLLICIT' ) ;# server to client
	private $COMMANDS = array( 
		0 => 'RUDP_CMD_NOOP',
		1 => 'RUDP_CMD_CLOSE',
		2 => 'RUDP_CMD_CONN_REQ',
		3 => 'RUDP_CMD_CONN_RSP',
		4 => 'RUDP_CMD_PING',
		5 => 'RUDP_CMD_PONG' );
	private $FLAG = array(
		0 => 'None',
		1 => 'ACK',
		2 => 'RELIABLE',
		3 => 'ACK and RELIABLE',
		4 => 'RETRANSMITTED', 
		5 => 'RETRANSMITTED And ACK',
		7 => 'RELIABLE AND RETRANSMITTED' ,
		8 => 'UNRELIABLE',
		9 => 'UNRELIABLE And ACK',
		11 => 'UNRELIABLE AND RETRNASMITTED'
	);
	private $END_WAIT  = 10   ;   #Close Connection
	private $SDR_PORT  = 50007;
	private $RCV_PORT  = 50008;    # 50000-50010   
	public $SYN       = 0x40000000;
	private $ACK       = 0x20000000;
	private $FIN       = 0x10000000;
	private $DAT       = 0x08000000;
	private $SYN_ACK   = 0x04000000;
	private $ACK_DAT   = 0x02000000;
	private $FIN_ACK   = 0x01000000;
	private $MAX_PKTID = 0xffffff; 
    public function __construct($destAddr, $isClient){
        $this->destAddr = $destAddr;
        $this->wait     = $this->SYN_ACK;
		//if $isClient else $this->SYN
        $this->pktId    = 0;
        if(!$isClient){ 
            $this->accept = [$this->SYN]; #[$this->SYN, $this->DAT, $this->FIN]
            $this->time   = 0;
            $this->data   = '';
		}
	}
    public function checkTime($time){
            if($time - $this->time > $this->END_WAIT){
                return False;
	}
            return True;
	}
	public function rudpPacket($pktType = null, $pktId = null, $data = ''){
		return arary('pktType'=> $pktType, 'pktId'=> $pktId, 'data'=> $data);
	}
	public function processSYN($rudpPkt, $c){
		if(array_search($this->SYN, $c->accept)){
			if($c->wait == $this->SYN){ 
				$c->accept += [$this->DAT, $this->FIN];
			}
			$c->pktId = $rudpPkt['pktId'] + 1;
			$c->wait  = $this->DAT;
			$c->time  = time();
			return $this->rudpPacket($this->SYN_ACK, $c->pktId);
		}
		throw new Exception('processSYN', $rudpPkt)
	}
	public function processDAT($rudpPkt, $c){
		if($this->DAT == $c->wait){
			if($rudpPkt['pktId'] == $c->pktId){
				if(array_search($this->SYN,$c->accept){ 
					$c->accept->remove($this->SYN)
				}
				$c->pktId += 1;
				$c->data  += $rudpPkt['data'];
				$c->time   = time() ;
				return $this->rudpPacket($this->ACK, $c->pktId);
			}elseif($rudpPkt['pktId'] == $c->pktId - 1){ 
				$c->time   = time();
				return $this->rudpPacket($this->ACK, $c->pktId);
			}elseif $rudpPkt['pktId'] < $c->pktId - 1{ 
				throw new Exception('processDAT [Duplicated]', $rudpPkt) 
			}# Bugs
		}
		throw new Exception('processDAT', $rudpPkt);
	}
	public function processFIN($rudpPkt, $c){
		if(array_search($this->FIN, $c->accept) and $rudpPkt['pktId'] == $c->pktId){
			if(array_search($this->DAT, $c->accept)){ 
				$c->accept->remove($this->DAT);
			}
			$c->wait = $this->FIN;
			$c->time = time();
			return $$this->rudpPacket($this->FIN_ACK, $c->pktId + 1);
		}
		throw new Exception('processFIN', $rudpPkt);
	}
	public function processSYN_ACK($rudpPkt, $c){
		if($this->SYN_ACK == $c->wait and $rudpPkt['pktId'] == $c->pktId + 1){
			$c->wait = $this->ACK;
			$c->pktId += 1;
			return $this->rudpPacket($this->DAT, $c->pktId)
		}
		throw new Exception('processSYN_ACK', $rudpPkt);
	}
	public function processACK($rudpPkt, $c){
		if($this->ACK == $c->wait and $rudpPkt['pktId'] == $c->pktId + 1){
			$c->pktId += 1;
			return $this->rudpPacket($this->DAT, $c->pktId);
		}
		throw new Exception('processACK', $rudpPkt);
	}
	public function processFIN_ACK($rudpPkt, $c){
		if(this->FIN_ACK == $c.wait and $rudpPkt['pktId'] == $c.pktId + 1){
			$c.pktId += 1
			throw new Exception( $c);
		}
		throw new Exception('processFIN_ACK', $rudpPkt);
	}
	public function encode($rudpPkt){ #pktId can be either ACK # or SEQ #
		if($rudpPkt['pktId'] <= $MAX_PKTID){
			$header = $rudpPkt['pktType'] | $rudpPkt['pktId'];
			return pack('i', $header) + $rudpPkt['data'];
		}
		throw new Exception();
	}
	public function decode($bitStr){
	    if (count($bitStr) < 4)
		throw new Exception();
		else{
			$header  = unpack('i', $bitStr[4])[0]
			return $this->rudpPacket($header & 0x7f000000, $header & 0x00ffffff, $bitStr[4]);
		}
	}
}	
class client{
	private $MAX_DATA  = 1004;
	private $MAX_RESND = 3;
	private $RTO       = 3   ;    #The retransmission time period
    public function __construct($address, $port){
        $this->socketip=$address;
        $this->socketport=$port;
        $this->skt = socket_create(AF_INET, SOCK_DGRAM, 0); #UDP
        
        $this->skt->connect($address,$port);
        #$this->skt->bind(('', $srcPort)); #used for recv
    }
    public function connect(){
        $this->conn = new rudpConnection($this->socketip, $this->socketport);
        socket_set_block($this->skt);
        #$this->skt->settimeout(RTO)
        while($this->MAX_RESND == $i){
            try{ 
				$data=$this->conn->encode($this->conn->rudpPacket($this->SYN, $pktId));
				socket_sendto($this->skt,$data,strlen($data),0,$this->socketip);
                //print $this->rudpPacket(SYN, $this->conn->pktId)     ## For debugging
             
                while(True){
					$from = '';
					$port = 0;
					socket_recvfrom($this->skt, $recvData, $this->MAX_DATA, 0, $from, $port);
                    try{ 
                        $recvPkt = $this->conn->decode($recvData);
                        //$sendPkt = rudpProcessSwitch[$recvPkt['pktType']]($recvPkt, $this->conn);
                        return True;
					}catch(){
						continue;
					}
				}
			}
            catch timeout{ continue;}
            catch Exception as e { 
               // print e->message
                //print '[Handshaking] unexpected error occurs\n' ## For debugging
                return False;
			}
			$i++;
		}
		throw new Exception();
	}
}
?>
