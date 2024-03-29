<?php


namespace app\admin\controller;

use app\admin\model\RolePower;
use Exception;
use think\facade\Request;
use think\response\Json;
use think\response\View;

/**
 * 角色控制器
 * @package app\admin\controller
 * @author RonaldoC
 * @version 1.0.0
 * @date 2019-8-10 12:17:26
 */
class Role extends Base
{
    /**
     * 角色列表查询
     * @throws Exception
     * @author RonaldoC
     * @version 1.0.0
     * @date 2019-8-10 13:51:58
     */
    public function index()
    {
        // 请求参数
        $params = $this->request->param();

        // 查询数据
        $role = new \app\admin\model\Role();
        $list = $role->listByPage($params);
        $page = $list->render();

        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->assign('params', $params);
        return view();
    }

    /**
     * 角色新增
     * @return Json|View          [GET请求返回页面，POST请求返回JSON]
     * @throws Exception
     * @author RonaldoC
     * @version 1.0.0
     * @date 2019-8-10 13:52:07
     */
    public function add()
    {
        if (Request::isAjax()) {
            if (Request::isGet()) {
                return $this->returnJson(-1, '非法请求！');
            }

            $params = input('post.');

            // 参数校验
            $validate = new \app\admin\validate\Role();
            $result = $validate->check($params);
            if (!$result) {
                return $this->returnJson(-1, $validate->getError());
            }

            // 数据校验
            $mRole = new \app\admin\model\Role();
            $role_list = $mRole->listByCondition([]);
            if (count($role_list) > 0) {
                foreach ($role_list as $v) {
                    if ($v['name'] == $params['name']) {
                        return $this->returnJson(-1, '角色名称已存在！');
                    }
                }
            }

            // 启动事物
            $Db = $mRole->db(false);
            $Db->startTrans();
            try {
                $res_flag = $mRole->add($params);
                if (!$res_flag) {
                    // 回滚事物
                    $Db->rollback();
                    return $this->returnJson(-1, "新增失败！");
                }

                // 提交事物
                $Db->commit();
            } catch (Exception $e) {
                // 回滚事物
                $Db->rollback();
                return $this->returnJson(-1, "新增失败！");
            }

            return $this->returnJson(1, '新增成功！');
        } else {
            if (Request::isPost()) {
                return $this->returnJson(-1, '非法请求！');
            }
            return view();
        }
    }

    /**
     * 角色编辑
     * @return Json|View          [GET请求返回页面，POST请求返回JSON]
     * @throws Exception
     * @author RonaldoC
     * @version 1.0.0
     * @date 2019-8-10 13:52:20
     */
    public function edit()
    {
        if (Request::isAjax()) {
            if (Request::isGet()) {
                return $this->returnJson(-1, '非法请求！');
            }

            $params = input('post.');

            // 参数校验
            if (!isset($params['id'])) {
                return $this->returnJson(-1, '缺少请求参数！');
            }
            $validate = new \app\admin\validate\Role();
            $result = $validate->check($params);
            if (!$result) {
                return $this->returnJson(-1, $validate->getError());
            }

            // 数据校验
            $mRole = new \app\admin\model\Role();
            $role = $mRole->getById($params['id']);
            if (!$role) {
                return $this->returnJson(-1, '请求参数错误！');
            }
            $role_list = $mRole->listByCondition([]);
            if (count($role_list) > 0) {
                foreach ($role_list as $v) {
                    if ($v['id'] != $params['id'] && $v['name'] == $params['name']) {
                        return $this->returnJson(-1, '角色名称已存在！');
                    }
                }
            }

            $role['name'] = $params['name'];
            $role['desc'] = $params['desc'];

            // 启动事物
            $Db = $mRole->db(false);
            $Db->startTrans();
            try {
                $res_flag = $mRole->edit($role);
                if (!$res_flag) {
                    // 回滚事物
                    $Db->rollback();
                    return $this->returnJson(-1, "编辑失败！");
                }

                // 提交事物
                $Db->commit();
            } catch (Exception $e) {
                // 回滚事物
                $Db->rollback();
                return $this->returnJson(-1, "编辑失败！");
            }

            return $this->returnJson(1, '编辑成功！');
        } else {
            if (Request::isPost()) {
                return $this->returnJson(-1, '非法请求！');
            }

            $params = input('get.');

            // 参数校验
            if (!isset($params['id'])) {
                return $this->returnJson(-1, '缺少请求参数！');
            }

            // 数据校验
            $mRole = new \app\admin\model\Role();
            $role = $mRole->getById($params['id']);
            if (!$role) {
                return $this->returnJson(-1, '请求参数错误！');
            }

            // 返回参数
            $this->assign('role', $role);
            return view('add');
        }
    }

    /**
     * 角色停用
     * @return Json|void          [GET请求返回页面，POST请求返回JSON]
     * @throws Exception
     * @author RonaldoC
     * @version 1.0.0
     * @date 2019-8-10 13:54:41
     */
    public function roleStop()
    {
        if (Request::isAjax()) {
            if (Request::isGet()) {
                return $this->returnJson(-1, '非法请求！');
            }

            $params = input('post.');

            // 参数校验
            if (!isset($params['id'])) {
                return $this->returnJson(-1, '缺少请求参数！');
            }

            // 数据校验
            $mRole = new \app\admin\model\Role();
            $role = $mRole->getById($params['id']);
            if (!$role) {
                return $this->returnJson(-1, '请求参数错误！');
            }

            // 停用
            if ($role['enabled'] === 1) {
                $role['enabled'] = 0;

            } else {  // 启用
                $role['enabled'] = 1;
            }

            // 启动事物
            $Db = $mRole->db(false);
            $Db->startTrans();
            try {
                $res_flag = $mRole->edit($role);
                if (!$res_flag) {
                    // 回滚事物
                    $Db->rollback();
                    return $this->returnJson(-1, "操作失败！");
                }

                // 提交事物
                $Db->commit();
            } catch (Exception $e) {
                // 回滚事物
                $Db->rollback();
                return $this->returnJson(-1, "操作失败！");
            }

            return $this->returnJson(1, '操作成功！');
        } else {
            return $this->error('非法请求！');
        }
    }

    /**
     * 角色删除
     * @return Json|void          [GET请求返回页面，POST请求返回JSON]
     * @throws Exception
     * @author RonaldoC
     * @version 1.0.0
     * @date 2019-8-10 16:51:45
     */
    public function delete()
    {
        if (Request::isAjax()) {
            if (Request::isGet()) {
                return $this->returnJson(-1, '非法请求！');
            }

            $params = input('post.');

            if (isset($params['ids'])) { // 批量删除

                // 数据校验
                $id_arr = explode(',', $params['ids']);
                $condition[] = ['r.id', 'in', $params['ids']];
                $mRole = new \app\admin\model\Role();
                $role_list = $mRole->listByCondition($condition);
                if (count($id_arr) != count($role_list)) {
                    return $this->returnJson(-1, '请求参数错误！');
                }

                $mUser = new \app\admin\model\User();
                foreach ($role_list as $v) {
                    $temp_list = $mUser->listByCondition([['role_id', '=', $v['id']], ['locked', '=', 0]]);
                    if (count($temp_list) > 0) {
                        return $this->returnJson(-1, '请先移除角色下的用户再进行删除操作！');
                    }
                }

                // 启动事物
                $Db = $mRole->db(false);
                $Db->startTrans();
                try {
                    $count = $mRole->batchDelete($params['ids']);
                    if ($count != count($role_list)) {
                        // 回滚事物
                        $Db->rollback();
                        return $this->returnJson(-1, "删除失败！");
                    }

                    // 删除角色与权限关联记录
                    $mRolePower = new RolePower();
                    $mRolePower->deleteByRoleIds($params['ids']);

                    // 提交事物
                    $Db->commit();
                } catch (Exception $e) {
                    // 回滚事物
                    $Db->rollback();
                    return $this->returnJson(-1, "删除失败！");
                }
            } else { // 删除单个

                // 参数校验
                if (!isset($params['id'])) {
                    return $this->returnJson(-1, '缺少请求参数！');
                }

                // 数据校验
                $mRole = new \app\admin\model\Role();
                $role = $mRole->getById($params['id']);
                if (!$role) {
                    return $this->returnJson(-1, '请求参数错误！');
                }

                $mUser = new \app\admin\model\User();
                $user_list = $mUser->listByCondition([['role_id', '=', $params['id']], ['locked', '=', 0]]);
                if (count($user_list) > 0) {
                    return $this->returnJson(-1, '请先移除该角色下的用户再进行删除操作！');
                }

                // 启动事物
                $Db = $mRole->db(false);
                $Db->startTrans();
                try {
                    $res_flag = $role->delete();
                    if (!$res_flag) {
                        // 回滚事物
                        $Db->rollback();
                        return $this->returnJson(-1, "删除失败！");
                    }

                    // 删除角色与权限关联记录
                    $mRolePower = new RolePower();
                    $mRolePower->deleteByRoleId($params['id']);

                    // 提交事物
                    $Db->commit();
                } catch (Exception $e) {
                    // 回滚事物
                    $Db->rollback();
                    return $this->returnJson(-1, "删除失败！");
                }
            }

            return $this->returnJson(1, '删除成功！');
        } else {
            return $this->error('非法请求！');
        }
    }

    /**
     * 分配权限
     * @return Json|View          [GET请求返回页面，POST请求返回JSON]
     * @throws Exception
     * @author RonaldoC
     * @version 1.0.0
     * @date 2019-8-29 20:20:04
     */
    public function distributePower()
    {
        if (Request::isAjax()) {
            if (Request::isGet()) {
                return $this->returnJson(-1, '非法请求！');
            }

            $params = input('post.');

            // 参数校验
            if (!isset($params['id']) || !isset($params['ids'])) {
                return $this->returnJson(-1, '缺少请求参数！');
            }
            if (empty($params['ids'])) {
                return $this->returnJson(-1, '请勾选至少一个选项！');
            }

            // 数据校验
            $mRole = new \app\admin\model\Role();
            $role = $mRole->getById($params['id']);
            if (!$role) {
                return $this->returnJson(-1, '请求参数错误！');
            }

            $id_arr = explode(',', $params['ids']);
            $condition[] = ['p.id', 'in', $params['ids']];
            $mPower = new \app\admin\model\Power();
            $power_list = $mPower->listByCondition($condition);
            if (count($id_arr) != count($power_list)) {
                return $this->returnJson(-1, '请求参数错误！');
            }

            $arr = [];
            foreach ($id_arr as $v) {
                $arr[] = [
                    'role_id' => $params['id'],
                    'power_id' => $v
                ];
            }

            // 启动事物
            $Db = $mRole->db(false);
            $Db->startTrans();
            try {
                $mRolePower = new RolePower();

                // 先删除后添加
                $mRolePower->deleteByRoleId($params['id']);

                $ret = $mRolePower->saveAll($arr);
                if (count($ret) != count($arr)) {
                    // 回滚事物
                    $Db->rollback();
                    return $this->returnJson(-1, "操作失败！");
                }

                // 提交事物
                $Db->commit();
            } catch (Exception $e) {
                // 回滚事物
                $Db->rollback();
                return $this->returnJson(-1, "操作失败！");
            }

            return $this->returnJson(1, '操作成功！');
        } else {
            if (Request::isPost()) {
                return $this->returnJson(-1, '非法请求！');
            }

            $params = input('get.');

            // 参数校验
            if (!isset($params['id'])) {
                return $this->returnJson(-1, '缺少请求参数！');
            }

            // 数据校验
            $mRole = new \app\admin\model\Role();
            $role = $mRole->getById($params['id']);
            if (!$role) {
                return $this->returnJson(-1, '请求参数错误！');
            }

            // 返回参数
            $this->assign('role', $role);
            return view('distribute');
        }
    }

    /**
     * 角色权限查询
     * @return Json|void          [GET请求返回页面，POST请求返回JSON]
     * @throws Exception
     * @author RonaldoC
     * @version 1.0.0
     * @date 2019-8-26 19:10:22
     */
    public function selectPower()
    {
        if (Request::isAjax()) {
            if (Request::isPost()) {
                return $this->returnJson(-1, '非法请求！');
            }

            $params = input('get.');

            // 参数校验
            if (!isset($params['id'])) {
                return $this->returnJson(-1, '缺少请求参数！');
            }

            // 数据校验
            $mRole = new \app\admin\model\Role();
            $role = $mRole->getById($params['id']);
            if (!$role) {
                return $this->returnJson(-1, '请求参数错误！');
            }

            $mPower = new \app\admin\model\Power();
            $power_list = $mPower->listByCondition([]);

            if (count($power_list) > 0) {
                // 查询角色已有权限
                $mRolePower = new RolePower();
                $list = $mRolePower->listByCondition(['role_id' => $params['id']]);
                $arr = [];
                if (count($list) > 0) {
                    $arr = $list->toArray();
                    $arr = array_column($arr, 'power_id');
                }

                $power_arr = $power_list->toArray();
                $power_arr = array_map(function ($v) use ($arr) {
                    $v['label'] = $v['name'];

                    // 勾选角色已有权限
                    if (in_array($v['id'], $arr)) {
                        $v['checked'] = true;
                    }
                    return $v;
                }, $power_arr);
                $power_arr = $this->treeJson($power_arr);
                $arr = [
                    'code' => 0,
                    'data' => $power_arr,
                ];
                return json($arr);
            }
        } else {
            return $this->error('非法请求！');
        }
    }

    /**
     * 父子级树
     * @param $arr array          [待排列的数据]
     * @return array              [已排列的数据]
     * @author RonaldoC
     * @version 1.0.0
     * @date 2019-8-28 19:33:13
     */
    private function treeJson($arr)
    {
        $arr1 = array_filter($arr, function ($v) {
            return $v['par_id'] == 0;
        });
        $arr1 = array_values($arr1);
        if (count($arr1) > 0) {
            foreach ($arr1 as &$v) {
                $par_id = $v['id'];
                $arr2 = array_filter($arr, function ($v) use ($par_id) {
                    return $v['par_id'] == $par_id;
                });
                $arr2 = array_values($arr2);
                if (count($arr2) > 0) {
                    $v['children'] = $arr2;
                    foreach ($v['children'] as &$v2) {
                        $par_id2 = $v2['id'];
                        $arr3 = array_filter($arr, function ($v) use ($par_id2) {
                            return $v['par_id'] == $par_id2;
                        });
                        $arr3 = array_values($arr3);
                        if (count($arr3) > 0) {
                            $v2['children'] = $arr3;
                        }
                    }
                }
            }
        }
        return $arr1;
    }

    /**
     * 授予用户角色
     * @return Json|View          [GET请求返回页面，POST请求返回JSON]
     * @throws Exception
     * @author RonaldoC
     * @version 1.0.0
     * @date 2019-9-2 19:49:11
     */
    public function authorizeRole()
    {
        if (Request::isAjax()) {
            if (Request::isGet()) {
                return $this->returnJson(-1, '非法请求！');
            }

            $params = input('post.');

            // 参数校验
            if (!isset($params['id']) || !isset($params['ids'])) {
                return $this->returnJson(-1, '缺少请求参数！');
            }
//            if (empty($params['ids'])) {
//                return $this->returnJson(-1, '请选择至少一个用户！');
//            }

            // 数据校验
            $mRole = new \app\admin\model\Role();
            $role = $mRole->getById($params['id']);
            if (!$role) {
                return $this->returnJson(-1, '请求参数错误！');
            }

            $mUser = new \app\admin\model\User();
            $user_arr = [];
            if ($params['ids']) {
                $id_arr = explode(',', $params['ids']);
                $condition[] = ['u.id', 'in', $params['ids']];
                $user_list = $mUser->listByCondition($condition);
                if (count($id_arr) != count($user_list)) {
                    return $this->returnJson(-1, '请求参数错误！');
                }

                if (count($user_list) > 0) {
                    foreach ($user_list as $v) {
                        $user_arr[] = ['id' => $v['id'], 'role_id' => $params['id']];
                    }
                }
            }


            // 启动事物
            $Db = $mRole->db(false);
            $Db->startTrans();
            try {

                // 先删除后添加
                $user_list_by_role_id = $mUser->listByCondition([['role_id', '=', $params['id']]]);
                if (count($user_list_by_role_id) > 0) {
                    $user_arr2 = [];
                    foreach ($user_list_by_role_id as $v) {
                        $user_arr2[] = ['id' => $v['id'], 'role_id' => ''];
                    }
                    $mUser->saveAll($user_arr2);
                }

                $ret = $mUser->saveAll($user_arr);
                if (count($ret) != count($user_arr)) {
                    // 回滚事物
                    $Db->rollback();
                    return $this->returnJson(-1, "操作失败！");
                }

                // 提交事物
                $Db->commit();
            } catch (Exception $e) {
                // 回滚事物
                $Db->rollback();
                return $this->returnJson(-1, "操作失败！");
            }

            return $this->returnJson(1, '操作成功！');
        } else {
            if (Request::isPost()) {
                return $this->returnJson(-1, '非法请求！');
            }

            $params = input('get.');

            // 参数校验
            if (!isset($params['id'])) {
                return $this->returnJson(-1, '缺少请求参数！');
            }

            // 数据校验
            $mRole = new \app\admin\model\Role();
            $role = $mRole->getById($params['id']);
            if (!$role) {
                return $this->returnJson(-1, '请求参数错误！');
            }

            // 查询系统当前有效用户
            $mUser = new \app\admin\model\User();
            $user_list = $mUser->listByCondition([['locked', '=', '0']]);

            // 获取选中用户
            $selected = [];
            if (count($user_list) > 0) {
                foreach ($user_list as $v) {
                    if ($v['role_id'] == $params['id']) {
                        $selected[] = $v['id'];
                    }
                }
                $role['ids'] = implode(',', $selected);
            }

            // 返回参数
            $this->assign('role', $role);
            $this->assign('user_list', $user_list);
            $this->assign('selected_user_list', $selected);
            return view('authorize');
        }
    }
}


