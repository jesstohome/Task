<?php

namespace app\index\controller;

//use think\Controller;
use Request;
use library\Controller;
use think\Db;

class Support extends Controller
{
    /**
     * 首页
     */
    public function index()
    {
        $token= Request::instance()->header('TOKEN');
        $tokenData = Db::table("xy_token")->where("token",$token)->order("time desc")->find();
        $uid = $tokenData['uid'];
        $data['info'] = db('xy_cs')->where('status', 1)->select();
        if (config('open_agent_chat') == 1) {
            $service = model('admin/Users')->get_user_service_id($uid);
           
            if ($service) {
                foreach ($data['info'] as $k => $v) {
                    $data['info'][$k]['url'] = $service['chats'];
                }
            }
        }
        $list = $data['info'];
        
        //$data["msg"] = db('xy_index_msg')->where('status', 1)->select();
        
        return json(['code' => 0, 'info' => yuylangs('czcg'), 'data' => $list]);
    }


    public function index2()
    {
        $this->url = isset($_REQUEST['url']) ? $_REQUEST['url'] : '';
        return $this->fetch();
    }

    /**
     * 首页
     */
    public function detail()
    {
        $id = input('get.id/d', 1);
        $this->info = db('xy_index_msg')->where('id', $id)->find();


        return $this->fetch();
    }


    /**
     * 换一个客服
     */
    public function other_cs()
    {
        $data = db('xy_cs')->where('status', 1)->where('id', '<>', $id)->find();
        if ($data) return json(['code' => 0, 'info' => lang('czcg'), 'data' => $data]);
        return json(['code' => 1, 'info' => lang('zwsj')]);
    }
}