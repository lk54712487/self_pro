<?php
if(!defined('PROJECT_NAME')) die('project empty');
/*
	称号
*/
class titleControl extends sysControl{
	
	public function title_list(){
		$selected = selected(array('is_open'));
		$Mtitle = M('title');
		$is_del = array('is_del' => '0');
		$page= isset($_POST['page']) && !empty($_POST['page']) ? intval($_POST['page']) : (isset($_GET['page']) && !empty($_GET['page']) ? intval($_GET['page']) : 1);
		$num = 20;  	//显示的数量
		$where = search('name');
		$where2 = array();
		if($selected['is_open']){
			$is_show = explode('/',$selected['is_open']);
			if(isset($is_show[1]) && !empty($is_show[1])){
				$where2 = array(
					'is_open' => $is_show[1],
				);
			}
		}
		
		$title = $Mtitle
				 ->where($is_del)
				 ->where($where)
				 ->where($where2)
				 ->page($page,$num)->select();
		$count = $Mtitle
				 ->where($is_del)
				 ->where($where)
				 ->where($where2)
				 ->count();
		self::output('count',$count);
		$page_obj = new page($count,$num,$page,'javascript:;',5);
		$page_obj ->page_attr();
		$page_obj ->conf = 23456;
		self::output('page',$page_obj ->show());
	//	$_POST['keyword'] = isset($_POST['keyword']) ? $_POST['keyword'] : '';
		self::form_top(array(
			/*
			'selected' => array(	//下拉选择
				$selected['is_open'] => array( 	//字段二
					'0' => '请选择',
					'1' => '开启',
					'2' => '关闭',
				),
			),
			*/
			'keyword' => I('keyword'),
			'add' => '?act=title&op=title_edit',
			'search',
		));
		
		self::form_list(array(
			array('label','id','ID',array('style'=>'max-width:300px;max-height:300px;')),
			array('label','name','称号名称'),
			array('time','create_time','创建时间'),
			array('menu',array(
					array('编辑','javascript:;',array('style'=>'background:#FF5722','onclick' => "question_edit('编辑','?act=title&op=title_edit&id=__ID__','','','')")),
					array('删除','javascript:;',array('onclick' => "question_del(this,'?act=title&op=title_del&id=__ID__')")),
					),'操作'),
		),$title,'id');
	}
	
	public function title_edit(){
		if($_POST){
			$this->commit();
		}
		$title = '';
		if(isset($_GET['id']) && $_GET['id'] > 0){
			$title = M('title')->where(array('id' => intval($_GET['id'])))->find();
		}
		
		self::form("this",array(
			array('hidden','id','ID'),
			array('text','name','称号名称'),
		),$title,'post','public_form');
	}
	
	public function title_del(){
		$id = intval($_GET['id']);
		if($id){
			$res = M('title')->where(array('id' => $id))->update(array('is_del' => '1'));
			if($res){
				show_message(array('code' => '1' , 'msg' => '删除成功'),'json');
			}else{
				show_message(array('code' => '-1' , 'msg' => '删除失败'),'json');
			}
		}
	}
	
	private function commit(){
		$field = array('id','name');
		$table = new table('title');
		$res = $table
			  ->field($field)
			  ->type('id','auto_key')		//主键
			  ->other('add',array('create_time' => time() ))  //添加的时候附加的值
			  ->commit();
		$data = $table->get_state();
		
		$log = array(
			'admin_id' => $_SESSION['admin']['id'],
			'create_time' => time(),
			'ip' => getIp(),
		);
		
		if(!empty($data) && $data['M'] == 'add'){
			$u = M('title')->where(array('id' => $res))->find();
			$log['other'] = $_SESSION['admin']['username'].'在'.date('Y-m-d H:i:s').'时添加了帮派,id是'.$res;
		}else if(!empty($data) && $data['M'] == 'update'){
			$u = M('title')->where(array('id' => $_POST['id']))->find();
			$log['other'] = $_SESSION['admin']['username'].'在'.date('Y-m-d H:i:s').'时修改了帮派,id是'.intval($_POST['id']);
		}
		M('admin_log')->add($log);
		if($res){
			show_message(array('code' => '1' ,'msg' => '操作成功'),'json');
		}else{
			show_message(array('code' => '-1' ,'msg' => '操作失败'),'json');
		}
	}
}
?>