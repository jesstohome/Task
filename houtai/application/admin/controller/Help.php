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

use app\admin\service\NodeService;
use library\Controller;
use library\tools\Data;
use think\Db;

/**
 * 帮助中心
 * Class Users
 * @package app\admin\controller
 */
class Help extends Base
{

    /**
     * 公告管理
     * @auth true
     * @menu true
     */
    public function message_ctrl()
    {
        $this->title = lang('媒体管理');
        $this->_query('xy_message')->page();
    }

    /**
     * 添加公告
     * @auth true
     * @menu true
     */
    public function add_message()
    {
        $this->title = lang('添加公告');
        if (request()->isPost()) {
            $this->applyCsrfToken();
            $title = input('post.title/s', '');
            $content = input('post.content/s', '');

            if (!$title) $this->error(lang('标题为必填项'));
            if (mb_strlen($title) > 50) $this->error(lang('标题长度限制为50个字符'));
            if (!$content) $this->error(lang('公告内容为必填项'));

            $res = Db::table('xy_message')->insert(['addtime' => time(), 'sid' => 0, 'type' => 3, 'title' => $title, 'content' => $content]);
            if ($res) {
                sysoplog('添加公告', json_encode($_POST, JSON_UNESCAPED_UNICODE));
                $this->success(lang('发送公告成功'), '#' . url('message_ctrl'));
            } else
                $this->error(lang('发送公告失败'));
        }
        return $this->fetch();
    }

    /**
     * 编辑公告
     * @auth true
     * @menu true
     */
    public function edit_message($id)
    {
        $this->title = lang('编辑公告');
        $id = intval($id);
        if (request()->isPost()) {
            $this->applyCsrfToken();
            $title = input('post.title/s', '');
            $content = input('post.content/s', '');
            $id = input('post.id/d', 0);

            if (!$title) $this->error(lang('标题为必填项'));
            if (mb_strlen($title) > 50) $this->error(lang('标题长度限制为50个字符'));
            if (!$content) $this->error(lang('公告内容为必填项'));

            $res = Db::table('xy_message')->where('id', $id)->update(['addtime' => time(), 'type' => 3, 'title' => $title, 'content' => $content]);
            if ($res) {
                sysoplog('编辑公告', json_encode($_POST, JSON_UNESCAPED_UNICODE));
                $this->success(lang('编辑成功'), '#' . url('message_ctrl'));
            } else
                $this->error(lang('编辑失败'));
        }

        $info = Db::table('xy_message')->find($id);
        $this->assign('info', $info);
        $this->fetch();
    }

    /**
     * 删除公告
     * @auth true
     * @menu true
     */
    public function del_message()
    {
        $this->applyCsrfToken();
        $id = input('post.id/d', 0);
        $res = Db::table('xy_message')->where('id', $id)->delete();
        if ($res) {
            sysoplog('删除公告', json_encode($_POST, JSON_UNESCAPED_UNICODE));
            $this->success(lang('删除成功!'));
        } else
            $this->error(lang('删除失败!'));
    }

    /**
     * 前台首页文本
     * @auth true
     * @menu true
     */
    public function home_msg()
    {
        $this->title = lang('媒体管理');
        $this->_query('xy_index_msg')->page();
    }

    /**
     * 编辑前台首页文本
     * @auth true
     * @menu true
     */
    public function edit_home_msg($id)
    {
        $this->title = lang('编辑前台首页文本');
        $id = intval($id);
        if (request()->isPost()) {
            $id = input('post.id/d', 0);
            $title = input('post.title/s', '');

            $update['addtime'] = time();
            $update['title'] = $title;
            // 内容字段 — 6种语言
            $update['en_es'] = input('en_es');       // 英语 English
            $update['tw_tw'] = input('tw_tw');       // 法语 French
            $update['hy_hy'] = input('hy_hy');       // 德语 German
            $update['es_mx'] = input('es_mx');       // 西班牙语 Spanish
            $update['pt_br'] = input('pt_br');       // 葡萄牙语 Portuguese
            $update['rus_rus'] = input('rus_rus');   // 意大利语 Italian
            // 标题字段 — 6种语言
            $update['t_en_es'] = input('t_en_es');       // 英语 English
            $update['t_tw_tw'] = input('t_tw_tw');       // 法语 French
            $update['t_hy_hy'] = input('t_hy_hy');       // 德语 German
            $update['t_es_mx'] = input('t_es_mx');       // 西班牙语 Spanish
            $update['t_pt_br'] = input('t_pt_br');       // 葡萄牙语 Portuguese
            $update['t_rus_rus'] = input('t_rus_rus');   // 意大利语 Italian

            $res = Db::table('xy_index_msg')->where('id', $id)->update($update);
            if ($res) {
                unset($_POST['content']);
                sysoplog('编辑前台首页文本', $title);
                $this->success(lang('编辑成功'), '#' . url('home_msg'));
            } else
                $this->error(lang('编辑失败'));
        }

        $info = Db::table('xy_index_msg')->find($id);
        $this->assign('info', $info);
        $this->fetch();
    }

    /**
     * 首页轮播图
     * @auth true
     * @menu true
     */
    public function banner()
    {
//        if(request()->isPost()){
//            $image = input('post.image/s','');
//            if($image=='') $this->error(lang('请上传图片'));
//            $res = Db::name('xy_banner')->where('id',1)->update(['image'=>$image]);
//            if($res!==false)
//                $this->success(lang('操作成功'));
//            else
//                $this->error(lang('操作失败'));
//        }
//        $this->title = lang('轮播图设置');
//        $this->info = Db::name('xy_banner')->find(1);
//        $this->fetch();

        $this->title = lang('首页轮播图');
        $this->_query('xy_banner')->page();
    }

    /**
     * 编辑首页轮播图
     * @auth true
     * @menu true
     */
    public function edit_banner($id)
    {
        $this->title = lang('编辑首页轮播图');
        $id = intval($id);
        if (request()->isPost()) {
            $this->applyCsrfToken();
            $id = input('post.id/d', 0);
            $url = input('post.url/s', '');
            $image = input('post.image/s', '');

            if (!$image) $this->error(lang('图片为必填项'));

            $res = Db::table('xy_banner')->where('id', $id)->update(['image' => $image, 'url' => $url]);
            if ($res) {
                sysoplog('编辑首页轮播图', json_encode($_POST, JSON_UNESCAPED_UNICODE));
                $this->success(lang('编辑成功'), '#' . url('banner'));
            } else
                $this->error(lang('编辑失败'));
        }

        $info = Db::table('xy_banner')->find($id);
        $this->assign('info', $info);
        $this->fetch();
    }

    /**
     * 添加banner
     * @auth true
     * @menu true
     */
    public function add_banner()
    {
        $this->title = lang('添加首页轮播图');
        if (request()->isPost()) {
            $this->applyCsrfToken();
            $url = input('post.url/s', '');
            $image = input('post.image/s', '');

            //if(!$title)$this->error(lang('标题为必填项'));
            //if(mb_strlen($title) > 50)$this->error(lang('标题长度限制为50个字符'));
            if (!$url) $this->error(lang('图片为必填项'));

            $res = Db::table('xy_banner')->insert(['url' => $url, 'image' => $image]);
            if ($res) {
                sysoplog('添加首页轮播图', json_encode($_POST, JSON_UNESCAPED_UNICODE));
                $this->success(lang('提交成功'), '#' . url('banner'));
            } else
                $this->error(lang('提交失败'));
        }
        return $this->fetch();
    }

    public function del_banner()
    {
        //$this->applyCsrfToken();
        $id = input('post.id/d', 0);
        $res = Db::table('xy_banner')->where('id', $id)->delete();
        if ($res) {
            sysoplog('删除首页轮播图', json_encode($_POST, JSON_UNESCAPED_UNICODE));
            $this->success(lang('删除成功!'));
        } else
            $this->error(lang('删除失败!'));
    }


}