<?php
if(!defined('PROJECT_NAME')) die('project empty');
/*
	用户
*/
class userControl extends sysControl{
	
	public function user_list(){
		$selected = selected(array('is_show'));
		$Muser = M('user');
		$is_del = array('is_del' => '0');
		$page= isset($_POST['page']) && !empty($_POST['page']) ? intval($_POST['page']) : (isset($_GET['page']) && !empty($_GET['page']) ? intval($_GET['page']) : 1);
		$num = 10;  	//显示的数量
		$where = search('__AFFIX__user.phone');
		$user = $Muser
				 ->where($is_del)
				 ->where($where)
				 ->page($page,$num)
				 ->select();
		$count = $Muser
				 ->where($is_del)
				 ->where($where)
				 ->count();
		self::output('count',$count);
		$page_obj = new page($count,$num,$page,'javascript:;',5);
		$page_obj ->page_attr();
		$page_obj ->conf = 23456;
		self::output('page',$page_obj ->show());
		self::form_top(array(
		//	'add' => '?act=user&op=user_edit',
			'keyword' => I('keyword'),
			'search',
		));
		
		self::form_list(array(
			array('label','id','ID',array('style'=>'max-width:300px;max-height:300px;')),
			array('label','phone','用户名'),
			array('label','name','昵称'),
			array('image','image','图片'),
			array('time','create_time','创建时间'),
			array('menu',array(
					array('编辑','javascript:;',array('style'=>'background:#FF5722','onclick' => "question_edit('编辑','?act=user&op=user_edit&id=__ID__','','','')")),
					array('删除','javascript:;',array('onclick' => "question_del(this,'?act=user&op=user_del&id=__ID__')")),
					),'操作'),
		),$user,'id');
	}
	
	public function user_edit(){
		if($_POST){
			$this->commit();
		}
		$user = '';
		if(isset($_GET['id']) && $_GET['id'] > 0){
			$user = M('user')->where(array('id' => intval($_GET['id'])))->find();
		}
		$role_list = array();
		$role_list[] = '请选择称谓';
		$role = M('role')->where(array('is_del' => '0'))->select();
		if(!empty($role)){
			foreach($role as $key => $val){
				$role_list[$val['id']] = $val['name'];
			}
		}
		self::form("this",array(
			array('hidden','id','ID'),
			array('text','name','名称'),
			array('selected','role_id','称谓',$role_list),
		),$user,'post','public_form');
	}
	
	public function user_del(){
		$id = intval($_GET['id']);
		if($id){
			$res = M('user')->where(array('id' => $id))->update(array('is_del' => '1'));
			if($res){
				show_message(array('code' => '1' , 'msg' => '删除成功'),'json');
			}else{
				show_message(array('code' => '-1' , 'msg' => '删除失败'),'json');
			}
		}
	}
	
	private function commit(){
		$field = array('id','name','role_id');
		$table = new table('user');
		$res = $table
			  ->field($field)
			  ->type('id','auto_key')		//主键
			  ->other('add',array('create_time' => time()))  //添加的时候附加的值	//更新的时候附加的值
			  ->commit();
		$data = $table->get_state();
		if(!empty($data) && $data['M'] == 'add'){
			$u = M('user')->where(array('id' => $res))->find();
			$log = array(
				'admin_id' => $_SESSION['admin']['id'],
				'create_time' => time(),
				'ip' => getIp(),
				'other' => $_SESSION['admin']['username'].'在'.date('Y-m-d H:i:s').'时添加了友情链接,链接id是'.$res,
			);
			M('admin_log')->add($log);
		}else if(!empty($data) && $data['M'] == 'update'){
			$u = M('user')->where(array('id' => $_POST['id']))->find();
			$log = array(
				'admin_id' => $_SESSION['admin']['id'],
				'create_time' => time(),
				'ip' => getIp(),
				'other' => $_SESSION['admin']['username'].'在'.date('Y-m-d H:i:s').'时修改了友情链接,链接id是'.intval($_POST['id']),
			);
			M('admin_log')->add($log);
		}
		if($res){
			show_message(array('code' => '1' ,'msg' => '操作成功'),'json');
		}else{
			show_message(array('code' => '-1' ,'msg' => '操作失败'),'json');
		}
	}
}
?>