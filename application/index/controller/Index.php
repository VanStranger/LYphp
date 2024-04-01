<?php
namespace application\index\controller;

use ly\lib\Controller;
use \application\index\model as Model;
use \ly\lib\DB as DB;

class Index extends Controller
{
    public function __construct()
    {
        // echo "construct";
    }
    public $hook = [
        "pre",
    ];
    public function pre()
    {
        // return "pre";
        // var_dump(config("path_type"));
        // echo "pre";
    }
    public function mid()
    {
        // return "mid";
        // var_dump(config("path_type"));
        // echo "mid";
    }
    public function mid1()
    {
        // return "mid";
        // var_dump(config("path_type"));
        // echo "mid1";
    }
    public function index()
    {
        $Love    = new Model\Love();
        $hername = $Love->gethername();
        $data    = DB::table("users")->where("id", 10000)->select();
        $this->assign("hername", $hername);
        $this->assign("showhtml", '代码是:<p>Hello，{{ $hername}}。</p>');
        $this->displayHtml();
    }
    public function jsonapi($a)
    {
        $a    = 1;
        $data = DB::table("users")->where("id", 10000)->buildSql();
        // var_dump($data);
        $d = DB::table(["answer" => "a"])->join([$data, "m"], "a.authorid=m.id")->field("m.id,username,title")->select();
        return $d;
    }
    /** @Before mid
     * @Skip pre
     * @sd1 sd1
     */
    public function ceshi()
    {
        // $data=DB::table("users")->whereIn("id",[10000,10002,3])->select();
        // return ['data'=>$data,'sql'=>DB::getSql(),'params'=>DB::getParams()];
        set_time_limit(0);
        $data = DB::table("planeposs")->select();
        echo "len:" . count($data) . "\r\n";
        echo "start:" . $data[0]['id'] . "\r\n";
        $m = 0;
        for ($i = 0; $i < ceil(count($data) / 100); $i++) {
            $arr = [];
            // {"biz_code":"device_osd","data":{"host":{"elevation":0,"attitude_head":0,"latitude":23.16935,"attitude_roll":0,"attitude_pitch":0,"horizontal_speed":0,"longitude":113.4221833,"height":3},"sn":"15730564"},"timestamp":1695027551000}
            for ($j = 0; $j < 100; $j++) {
                if ($i * 100 + $j >= count($data)) {
                    break;
                }
                $json = json_decode($data[$i * 100 + $j]['info'], true);
                if (isset($json['data']['host']) && $json['data']['host']['latitude'] > 0 || $json['data']['host']['longitude'] > 0) {
                    $arr[] = ([$json['data']['sn'], $json['data']['host']['longitude'], $json['data']['host']['latitude'], $json['data']['host']['elevation'] ?: 0, $json['data']['host']['height'] ?: 0, $json['data']['host']['attitudeHead'] ?: 0, $json['data']['host']['attitude_roll'] ?: 0, $json['data']['host']['attitude_pitch'] ?: 0, $json['data']['host']['horizontal_speed'] ?: 0, $json['timestamp'] ?: 0, $data[$i * 100 + $j]['id']]);
                } else {
                    $m++;
                }
            }
            DB::execArray("update planeposs set sn=?,lng=?,lat=?,elevation=?,height=?,yaw=?,roll=?,pitch=?,speed=?,time=? where id=?", $arr);
        }

        echo $m;
    }
    public function updateTime()
    {
        // $data=DB::table("users")->whereIn("id",[10000,10002,3])->select();
        // return ['data'=>$data,'sql'=>DB::getSql(),'params'=>DB::getParams()];
        set_time_limit(0);
        // $ids=DB::table("planeposs")->where("time<0")->group("task_id")->field("task_id")->select();
        $ids = [
            ["task_id" => 56],
            // ["task_id" => 42],
            // ["task_id" => 59],
            // ["task_id" => 61],
        ];
        foreach ($ids as $key => $value) {
            $id        = $value['task_id'];
            $poss      = DB::table("planeposs")->where("task_id", $id)->select();
            $startTime = DB::table("task")->where("task_id", $id)->find();
            echo "id" . $id . "time" . $startTime['real_starttime'] . "\r\n";
            if (!$startTime) {
                echo "notime\r\n";
                continue;
            }
            $arr = [];
            if (count($poss) > 1) {
                foreach ($poss as $key => $value) {
                    $arr[] = [$startTime['real_starttime'] * 1000 + ($key) * 300, $value['id']];
                }
            }
            DB::execArray("update planeposs set time=? where id=?", $arr);
        }

        echo "end";
    }
    public function getDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6367; //地球半径Km

        $lat1 = ($lat1 * pi()) / 180;
        $lng1 = ($lng1 * pi()) / 180;

        $lat2 = ($lat2 * pi()) / 180;
        $lng2 = ($lng2 * pi()) / 180;

        $calcLongitude      = $lng2 - $lng1;
        $calcLatitude       = $lat2 - $lat1;
        $stepOne            = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
        $stepTwo            = 2 * asin(min(1, sqrt($stepOne)));
        $calculatedDistance = $earthRadius * $stepTwo;

        return floatval(round($calculatedDistance, 4));
    }

    //两点间距离比较远
    public function getLongDistance($lat1, $lng1, $lat2, $lng2)
    {
        $radius = 6378.137;
        $rad = floatval(M_PI / 180.0);

        $lat1 = floatval($lat1) * $rad;
        $lng1 = floatval($lng1) * $rad;
        $lat2 = floatval($lat2) * $rad;
        $lng2 = floatval($lng2) * $rad;
        if($lat1==$lat2 && $lng1==$lng2){
            return 0;
        }
        $theta = $lng2 - $lng1;

        $dist = acos(sin($lat1) * sin($lat2) + cos($lat1) * cos($lat2) * cos($theta));

        if ($dist < 0) {
            $dist += M_PI;
        }
        $dist = $dist * $radius;
        return $dist;
    }
    public function setDistance()
    {
        $ids = DB::table("planeposs")->where("lng!=0 and lat!=0")->group("task_id")->field("task_id")->select();
        // var_dump($ids);
        foreach ($ids as $key => $value) {
            $id   = $value['task_id'];
            $task = DB::table("task")->where("task_id", $value['task_id'])->find();
            // var_dump($task);
            if ($task && $task['distance'] == 0) {
                $poss     = DB::table("planeposs")->where("task_id", $id)->select();
                $lng      = 0;
                $lat      = 0;
                $distance = 0;
                if (count($poss) > 1) {
                    foreach ($poss as $key => $value) {
                        if ($lng && $lat) {
                            $distance += $this->getLongDistance($lat, $lng, $value['lat'], $value['lng']);
                            echo $lat.",". $lng.",". $value['lat'].",". $value['lng']."=>".$this->getLongDistance($lat, $lng, $value['lat'], $value['lng'])."\r\n";
                        }
                            $lng = $value['lng'];
                            $lat = $value['lat'];
                    }
                }
                var_dump("id" . $id . "dis" . $distance . "\r\n");
                echo "\r\n";
                DB::table("task")->where("task_id", $id)->update("distance", $distance);
            }
        }
    }
    public function setYaw()
    {
        set_time_limit(0);
        $data = DB::table("planeposs")->where("yaw>360 or roll>360 or pitch>360")->select();
        echo "len:" . count($data) . "\r\n";
        echo "start:" . $data[0]['id'] . "\r\n";
        $m = 0;
        for ($i = 0; $i < ceil(count($data) / 100); $i++) {
            $arr = [];
            // {"biz_code":"device_osd","data":{"host":{"elevation":0,"attitude_head":0,"latitude":23.16935,"attitude_roll":0,"attitude_pitch":0,"horizontal_speed":0,"longitude":113.4221833,"height":3},"sn":"15730564"},"timestamp":1695027551000}
            for ($j = 0; $j < 100; $j++) {
                if ($i * 100 + $j >= count($data)) {
                    break;
                }
                $json = json_decode($data[$i * 100 + $j]['info'], true);
                if (isset($json['data']['host']) && $json['data']['host']['latitude'] > 0 || $json['data']['host']['longitude'] > 0) {
                    $arr[] = ([$json['data']['sn'], $json['data']['host']['longitude'], $json['data']['host']['latitude'], $json['data']['host']['elevation'] ?: 0, $json['data']['host']['height'] ?: 0, $json['data']['host']['attitudeHead'] ?: 0, $json['data']['host']['attitude_roll'] ?: 0, $json['data']['host']['attitude_pitch'] ?: 0, $json['data']['host']['horizontal_speed'] ?: 0, $json['timestamp'] ?: 0, $data[$i * 100 + $j]['id']]);
                } else {
                    $m++;
                }
            }
            DB::execArray("update planeposs set sn=?,lng=?,lat=?,elevation=?,height=?,yaw=?,roll=?,pitch=?,speed=?,time=? where id=?", $arr);
        }

        echo $m;
    }
    public function viewTime(){
        $data=db("planeposs")->where("task_id",62)->order("id asc")->select();
        $time=0;

        foreach ($data as $key => $value) {
            if($time){
                echo $value['time']-$time<0?($value['time']-$time."\r\n"):"";    
            }
            $time=$value['time'];
            // echo $value['height']."\r\n";
        }
    }
}
