<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;

/**
 * Class UserController
 * @package app\admin\controller
 * @adminMenuRoot(
 *     'name'   => '管理组',
 *     'action' => 'default',
 *     'parent' => 'user/AdminIndex/default',
 *     'display'=> true,
 *     'order'  => 10000,
 *     'icon'   => '',
 *     'remark' => '管理组'
 * )
 */
class UserController extends AdminBaseController
{

    /**
     * 管理员列表
     * @adminMenu(
     *     'name'   => '管理员',
     *     'parent' => 'default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员管理',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $where = ["user_type" => 1];
        /**搜索条件**/
        $userLogin = $this->request->param('user_login');
        $userEmail = trim($this->request->param('user_email'));

        if ($userLogin) {
            $where['user_login'] = ['like', "%$userLogin%"];
        }

        if ($userEmail) {
            $where['user_email'] = ['like', "%$userEmail%"];;
        }
        $users = Db::name('user')
            ->where($where)
            ->order("id DESC")
            ->paginate(100);
        $users->appends(['user_loginss' => $userLogin, 'user_email' => $userEmail]);
        // 获取分页显示
        $page = $users->render();

        $rolesSrc = Db::name('role')->select();
        $roles    = [];
        foreach ($rolesSrc as $r) {
            $roleId           = $r['id'];
            $roles["$roleId"] = $r;
        }


        // // 添加不到user数组中 因为 user数组的获取方法是paginate 而不是 select
        // $num=null;
        // $street_office=Db::name('street_office');
        // foreach ($users as $key => $value) {
            
        //     $shiqu=$value['shiqu'];
        //     $s_info=$street_office->where(array('id'=>$shiqu))->find();
        //     $shiqu_name=$s_info['name'];
        //     $value['shiqu_name']=$shiqu_name;

        // }
        // // echo  $num;
        // print_r(json_encode( $users));
        // exit;


        $this->assign("page", $page);
        $this->assign("roles", $roles);
        $this->assign("users", $users);
        return $this->fetch();
    }

    /**
     * 管理员添加
     * @adminMenu(
     *     'name'   => '管理员添加',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员添加',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        $roles = Db::name('role')->where(['status' => 1])->order("id DESC")->select();
        $this->assign("roles", $roles);
        return $this->fetch();
    }

    /**
     * 管理员添加提交
     * @adminMenu(
     *     'name'   => '管理员添加提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员添加提交',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
            // yuyi
            $mz_type=$_POST['mz_type'];
            if($mz_type==0)
            {
                // 现在是没选择的作为全部市区的总账户  仅次于admin
                // $this->error("请选择录入账号级别！");
            }else if($mz_type==1&&$_POST['shiqu']=='-1')
            {
                $this->error("请选择 shiqu");
            }else if($mz_type==2&&$_POST['mz_street_office']=='-1')
            {
                $this->error("请选择 mz_street_office");
            }else if($mz_type==3&&$_POST['juweihui']=='-1')
            {
                $this->error("请选择 juweihui");
            }

            // yuyi
            $_POST['shiqu']=='-1'?$_POST['shiqu']='1':"";
            $_POST['mz_street_office']=='-1'? $_POST['mz_street_office']="1": "";
            $_POST['juweihui']=='-1'?$_POST['juweihui']=null:"";


        if ($this->request->isPost()) {
            if (!empty($_POST['role_id']) && is_array($_POST['role_id'])) {
                $role_ids = $_POST['role_id'];
                unset($_POST['role_id']);
                $result = $this->validate($this->request->param(), 'User');
                if ($result !== true) {
                    $this->error($result);
                } else {
                    $_POST['user_pass'] = cmf_password($_POST['user_pass']);

                    // yuyi
                    $post_arr=array();
                    $post_arr['user_login']=$_POST['user_login'];
                    $post_arr['user_pass']=$_POST['user_pass'];
                    $post_arr['user_email']=$_POST['user_email'];
                    $post_arr['mz_type']=$_POST['mz_type'];
                    $post_arr['shiqu']=$_POST['shiqu'];
                    $post_arr['mz_street_office']=$_POST['mz_street_office'];
                    $post_arr['juweihui']=$_POST['juweihui'];

                    $result             = DB::name('user')->insertGetId($post_arr);
                    if ($result !== false) {
                        //$role_user_model=M("RoleUser");
                        foreach ($role_ids as $role_id) {
                            if (cmf_get_current_admin_id() != 1 && $role_id == 1) {
                                $this->error("为了网站的安全，非网站创建者不可创建超级管理员！");
                            }
                            Db::name('RoleUser')->insert(["role_id" => $role_id, "user_id" => $result]);
                        }
                        $this->success("添加成功！", url("user/add"));
                    } else {
                        $this->error("添加失败！");
                    }
                }
            } else {
                $this->error("请为此用户指定角色！");
            }

        }
    }

    /**
     * 管理员编辑
     * @adminMenu(
     *     'name'   => '管理员编辑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员编辑',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $id    = $this->request->param('id', 0, 'intval');
        $roles = DB::name('role')->where(['status' => 1])->order("id DESC")->select();
        $this->assign("roles", $roles);
        $role_ids = DB::name('RoleUser')->where(["user_id" => $id])->column("role_id");
        $this->assign("role_ids", $role_ids);

        $user = DB::name('user')->where(["id" => $id])->find();
        // print_r(json_encode($user));
        // exit;
        $this->assign($user);
        return $this->fetch();
    }

    /**
     * 管理员编辑提交
     * @adminMenu(
     *     'name'   => '管理员编辑提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员编辑提交',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        // yuyi
            
            $mz_type=$_POST['mz_type'];
            if($mz_type==0)
            {
             // 现在是没选择的作为全部市区的总账户  仅次于admin
             // $this->error("录入账号类别！");
            }else if($mz_type==1&&$_POST['shiqu']=='-1')
            {
                $this->error("请选择 shiqu");
            }else if($mz_type==2&&$_POST['mz_street_office']=='-1')
            {
                $this->error("请选择 mz_street_office");
            }else if($mz_type==3&&$_POST['juweihui']=='-1')
            {
                $this->error("请选择 juweihui");
            }

            $_POST['shiqu']=='-1'?$_POST['shiqu']='1':"";
            $_POST['mz_street_office']=='-1'? $_POST['mz_street_office']="1": "";
            $_POST['juweihui']=='-1'?$_POST['juweihui']=null:"";
           

        if ($this->request->isPost()) {
            if (!empty($_POST['role_id']) && is_array($_POST['role_id'])) {
                if (empty($_POST['user_pass'])) {
                    unset($_POST['user_pass']);
                } else {
                    $_POST['user_pass'] = cmf_password($_POST['user_pass']);
                }
                $role_ids = $this->request->param('role_id/a');
                unset($_POST['role_id']);
                $result = $this->validate($this->request->param(), 'User.edit');

                if ($result !== true) {
                    // 验证失败 输出错误信息
                    $this->error($result);
                } else {
                    $result = DB::name('user')->update($_POST);
                    if ($result !== false) {
                        $uid = $this->request->param('id', 0, 'intval');
                        DB::name("RoleUser")->where(["user_id" => $uid])->delete();
                        foreach ($role_ids as $role_id) {
                            if (cmf_get_current_admin_id() != 1 && $role_id == 1) {
                                $this->error("为了网站的安全，非网站创建者不可创建超级管理员！");
                            }
                            DB::name("RoleUser")->insert(["role_id" => $role_id, "user_id" => $uid]);
                        }
                        $this->success("保存成功！");
                    } else {
                        $this->error("保存失败！");
                    }
                }
            } else {
                $this->error("请为此用户指定角色！");
            }

        }
    }

    /**
     * 管理员个人信息修改
     * @adminMenu(
     *     'name'   => '个人信息',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员个人信息修改',
     *     'param'  => ''
     * )
     */
    public function userInfo()
    {
        $id   = cmf_get_current_admin_id();
        $user = Db::name('user')->where(["id" => $id])->find();
        $this->assign($user);
        return $this->fetch();
    }

    /**
     * 管理员个人信息修改提交
     * @adminMenu(
     *     'name'   => '管理员个人信息修改提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员个人信息修改提交',
     *     'param'  => ''
     * )
     */
    public function userInfoPost()
    {
        if ($this->request->isPost()) {

            $data             = $this->request->post();
            $data['birthday'] = strtotime($data['birthday']);
            $data['id']       = cmf_get_current_admin_id();
            $create_result    = Db::name('user')->update($data);;
            if ($create_result !== false) {
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
        }
    }

    /**
     * 管理员删除
     * @adminMenu(
     *     'name'   => '管理员删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员删除',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $id = $this->request->param('id', 0, 'intval');
        if ($id == 1) {
            $this->error("最高管理员不能删除！");
        }

        if (Db::name('user')->delete($id) !== false) {
            Db::name("RoleUser")->where(["user_id" => $id])->delete();
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }

    /**
     * 停用管理员
     * @adminMenu(
     *     'name'   => '停用管理员',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '停用管理员',
     *     'param'  => ''
     * )
     */
    public function ban()
    {
        $id = $this->request->param('id', 0, 'intval');
        if (!empty($id)) {
            $result = Db::name('user')->where(["id" => $id, "user_type" => 1])->setField('user_status', '0');
            if ($result !== false) {
                $this->success("管理员停用成功！", url("user/index"));
            } else {
                $this->error('管理员停用失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    /**
     * 启用管理员
     * @adminMenu(
     *     'name'   => '启用管理员',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '启用管理员',
     *     'param'  => ''
     * )
     */
    public function cancelBan()
    {
        $id = $this->request->param('id', 0, 'intval');
        if (!empty($id)) {
            $result = Db::name('user')->where(["id" => $id, "user_type" => 1])->setField('user_status', '1');
            if ($result !== false) {
                $this->success("管理员启用成功！", url("user/index"));
            } else {
                $this->error('管理员启用失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }




/*  yuyi

    获取pid的地区

   http://localhost/minzheng/public/index.php/admin/user/getRegionList

*/

    public function getRegionList()
    {

        $id=isset($_POST['id'])?$_POST['id']:'';
        // $id=1;
        $street_office=Db::name('street_office');
        $street_office_list=$street_office->where(array('pid'=>$id))->field('id,name,pid')->select();
        // $json =null;
        // foreach ($street_office_list as $value) {
        //   $json .= json_encode($value) . ',';
        //   // $json .= json_encode($value['name']) . ',';
        //   // $json .= '"'.($value['name']) .'"'. ',';
        // } 
        // echo '[' . substr($json,0,strlen($json) - 1) . ']';

        // $this->assign('street_office_list',$street_office_list);



       // print_r(json_encode($street_office_list));
       echo json_encode($street_office_list);





    }





}