<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | www.xydai.cn 新源代网 
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 

// +----------------------------------------------------------------------

namespace app\admin\controller;

use library\service\NodeService;
use think\Db;
use think\exception\HttpResponseException;
use think\facade\Request;

/**
 * 系统参数配置
 * Class Config
 * @package app\admin\controller
 */
class Config extends Base
{
    /**
     * 默认数据模型
     * @var string
     */
    protected $table = 'SystemConfig';

    /**
     * 阿里云OSS上传点
     * @var array
     */
    protected $ossPoints = [
        'oss-cn-hangzhou.aliyuncs.com' => '华东 1 杭州',
        'oss-cn-shanghai.aliyuncs.com' => '华东 2 上海',
        'oss-cn-qingdao.aliyuncs.com' => '华北 1 青岛',
        'oss-cn-beijing.aliyuncs.com' => '华北 2 北京',
        'oss-cn-zhangjiakou.aliyuncs.com' => '华北 3 张家口',
        'oss-cn-huhehaote.aliyuncs.com' => '华北 5 呼和浩特',
        'oss-cn-shenzhen.aliyuncs.com' => '华南 1 深圳',
        'oss-cn-hongkong.aliyuncs.com' => '香港 1',
        'oss-us-west-1.aliyuncs.com' => '美国西部 1 硅谷',
        'oss-us-east-1.aliyuncs.com' => '美国东部 1 弗吉尼亚',
        'oss-ap-southeast-1.aliyuncs.com' => '亚太东南 1 新加坡',
        'oss-ap-southeast-2.aliyuncs.com' => '亚太东南 2 悉尼',
        'oss-ap-southeast-3.aliyuncs.com' => '亚太东南 3 吉隆坡',
        'oss-ap-southeast-5.aliyuncs.com' => '亚太东南 5 雅加达',
        'oss-ap-northeast-1.aliyuncs.com' => '亚太东北 1 日本',
        'oss-ap-south-1.aliyuncs.com' => '亚太南部 1 孟买',
        'oss-eu-central-1.aliyuncs.com' => '欧洲中部 1 法兰克福',
        'oss-eu-west-1.aliyuncs.com' => '英国 1 伦敦',
        'oss-me-east-1.aliyuncs.com' => '中东东部 1 迪拜',
    ];

    /**
     * 系统参数配置
     * @auth true
     * @menu true
     */
    public function info()
    {
        $this->title = '系统配置';
        $this->info = [];
        $this->language = Db::name('xy_language')->select();
       // dump($this->language);die;
        $this->fetch();
    }
    
    
    /**
     * 系统参数配置
     * @auth true
     * @menu true
     */
    public function infoEdit()
    {   
      // dump(input());die;
        $data = input('language', []);
        $langs = input('langs');
        if($langs){
            Db::table("xy_language")->where('1=1')->update(["moryuy"=>0]);
            Db::table("xy_language")->where(['id'=>$langs])->update(["moryuy"=>1]);
        }
            
        if(is_array($data) && count($data) > 0 ){
            
            Db::name('xy_language')->where('id','>',0)->update(['state'=>0]);
            
            $ids = []; 
            foreach ($data as $k=>$v){
               $ids[] = $k; 
            }
            
            count($ids) > 0 && Db::name('xy_language')->where('id','in',$ids)->update(['state'=>1]);
            return ['status'=>1,'msg'=>'修改成功'];
        }else{
            return ['status'=>0,'msg'=>'提交失败'];
        }
    }

    /**
     * 清理数据
     * @auth true
     * @menu true
     */
    public function clear()
    {
        $pwd = input('pwd/s');
        if ($pwd != '168168') {
            return $this->error('密码错误');
        }
       // $this->applyCsrfToken();
        $ids = input('data', '');

        $ids = json_decode($ids);
        $map[] = '1=1';
        Db::table('system_log')->where('id', '>', 0)->delete();
        Db::table('xy_deal_elog')->where('id', '>', 0)->delete();
        Db::table('xy_reads')->where('id', '>', 0)->delete();
        Db::table('xy_reward_log')->where('id', '>', 0)->delete();
        Db::table('xy_verify_msg')->where('tel', '>', 0)->delete();
        Db::table('xy_message')->where('id', '>', 0)->delete();
        if (in_array(1, $ids)) {
            Db::table('xy_users')->where('id', '>', 1)->delete();
            Db::table('xy_users_invites')->where('uid', '>', 1)->delete();
            Db::table('xy_users_setting')->where('id', '>', 0)->delete();
        }
        if (in_array(2, $ids)) {
            Db::table('xy_convey')->where('uid', '>', 1)->delete();
        }
        if (in_array(3, $ids)) {
            Db::table('xy_balance_log')->where('uid', '>', 1)->delete();
        }
        if (in_array(4, $ids)) {
            Db::table('xy_recharge')->where('uid', '>', 1)->delete();
        }
        if (in_array(5, $ids)) {
            Db::table('xy_deposit')->where('uid', '>', 1)->delete();
        }
        if (in_array(6, $ids)) {
            Db::table('xy_bankinfo')->where('uid', '>', 1)->delete();
        }
        if (in_array(7, $ids)) {
            Db::table('xy_member_address')->where('uid', '>', 1)->delete();
        }
        if (in_array(8, $ids)) {
            Db::table('xy_lixibao')->where('uid', '>', 1)->delete();
        }
        sysoplog('清理数据', '');
        $this->success('清理成功！');
    }

    /**
     * 修改系统能数配置
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function config()
    {
        $this->applyCsrfToken();
        if (Request::isGet()) {
            $this->fetch('system-config');
        }
        foreach (Request::post() as $key => $value) {
            sysconf($key, $value);
        }
        sysoplog('修改系统能数配置', json_encode($_POST, JSON_UNESCAPED_UNICODE));
        $this->success('系统参数配置成功！');
    }

    /**
     * 文件存储引擎
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function file()
    {
        $this->applyCsrfToken();
        if (Request::isGet()) {
            $this->type = input('type', 'local');
            $this->fetch("storage-{$this->type}");
        }
        $post = Request::post();
        if (isset($post['storage_type']) && isset($post['storage_local_exts'])) {
            $exts = array_unique(explode(',', strtolower($post['storage_local_exts'])));
            sort($exts);
            if (in_array('php', $exts)) $this->error('禁止上传可执行文件到本地服务器！');
            $post['storage_local_exts'] = join(',', $exts);
        }
        foreach ($post as $key => $value) sysconf($key, $value);
        if (isset($post['storage_type']) && $post['storage_type'] === 'oss') {
            try {
                $local = sysconf('storage_oss_domain');
                $bucket = $this->request->post('storage_oss_bucket');
                $domain = \library\File::instance('oss')->setBucket($bucket);
                if (empty($local) || stripos($local, '.aliyuncs.com') !== false) {
                    sysconf('storage_oss_domain', $domain);
                }
                $this->success('阿里云OSS存储配置成功！');
            } catch (HttpResponseException $exception) {
                throw $exception;
            } catch (\Exception $e) {
                $this->error("阿里云OSS存储配置失效，{$e->getMessage()}");
            }
        } else {
            sysoplog('文件存储引擎', json_encode($post, JSON_UNESCAPED_UNICODE));
            $this->success('文件存储配置成功！');
        }
    }
    

}
