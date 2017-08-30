<?php
namespace Home\Controller;
use Think\Controller;
use QL\QueryList;

class IndexController extends Controller {
	/**
	 * 获得详情里面的图片
	 */
    public function get_detail_pics(){
    	set_time_limit(0);
    	$goods = M('goods');
    	
    	$goodsList = $goods->select();
    	
    	$rules = array( // 采集规则
    			'img' => array('img', 'data-lazy')
    	);
    	
    	foreach ($goodsList as $glItem) {
    		$gid = $glItem['gid'];
    		
    		$html = sq_good_pics($gid);
    		 
    		$tempArr = QueryList::Query($html, $rules)->data; // 获得imgs数组
    		$imgs = array();
    		foreach ($tempArr as $item) {
    			$imgs[] = $item['img'];
    		}
    		$saveData['pics'] = array2string($imgs);
    		$goods->where(array('id' => $glItem['id']))->save($saveData);
    	}
    	
    }
    
    /**
     * 获得轮播图
     */
    public function get_slider_pics() {
    	set_time_limit(0);
    	$goods = M('goods');
    	
    	$goodsList = $goods->select();
    	
    	$rules = array( // 采集规则
    			'slider_img' => array('.fui-swipe-item>img', 'data-lazy')
    	);
    	
    	foreach ($goodsList as $glItem) {
    		$gid = $glItem['gid']; // 获得gid字段
    		
    		$html = file_get_contents( sq_good_url($glItem['gid']) );
    		
    		$tempArr = QueryList::Query($html, $rules)->data; // 获得轮播图
    		
    		$imgs = array();
    		
    		foreach ($tempArr as $item) {
    			$imgs[] = $item['slider_img'];
    		}
    		$saveData['slider_pics'] = array2string($imgs);
    		$goods->where(array('id' => $glItem['id']))->save($saveData);
    		
    	}
    	
    }
    
    /**
     * 由于数量少而且快，没有发生超时无响应，所以没有做分块操作
     */
    public function get_all_id() {
    	set_time_limit(0);
    	header('Content-type: text/html; charset=utf-8');
    	
    	$goods = M('goods');
    	
    	for ($i = 1; $i < 100; $i++) { // 这个i是页数，已经测试过它小于100
    		$result = sq_get_goods_list($i);
    		$jsonObj = json_decode($result, true); // 将Json解析为数组
    		 
    		$list = $jsonObj['result']['list'];
    		 
    		if (empty($jsonObj['result']['list'])) { // 如果里面为空的话就不再遍历
    			break;
    		} else { // 如果列表里面不为空，将其信息保存到数据库
    			foreach ($list as $item) {
    				$data['gid'] = $item['id'];
    				$data['title'] = $item['title'];
    				$goods->add($data);
    			}
    		}
    	}
    	
    	// $this->ajaxReturn(json_decode($result));
    }
    
    /**
     * 下载图片的前端显示，因为这个访问地址相对好找，所以就写了一个方法
     */
    public function download() {
    	$this->display();
    }
    
    public function download_logic() {
    	set_time_limit(0);
    	$type  = I('type', 'detail'); // 默认下载详情里面的图片，因为只有detail里面的图片和轮播图片，所以传参的时候传不是detail的话就会下载轮播图
    	$block = I('block', 0); // 分块操作的当前块，开始为0，因为之后的start需要是0为下标去操作数组
    	
    	$goods = M('goods');
    	
    	$blockCount = 50; // 当超过这个数量时，使用分块下载
    	
    	$goodsCount = $goods->count(); // 统计所有货物的总数
    	
    	$goodsList = $goods->select(); // 获得货物的列表数组
    	
    	$start = $block * $blockCount;
    	$next  = ($block + 1) * $blockCount;
    	
    	// 开始进行遍历，当i小于next和小于货物总数的时候递增
    	for ($i = $start; $i < $next && $i <= $goodsCount; $i ++){
    		$title = str_replace('/', '、', $goodsList[$i]['title']); // 防止多层文件夹
    		if ($type == 'detail') {
    			$path = iconv( 'utf-8', 'gbk', '图片/' . $title ); // 下载目录
    			$pics = string2array( $goodsList[$i]['pics'] ); // detail图
    		} else {
    			$path = iconv( 'utf-8', 'gbk', '图片/' . $title . '/轮播/' ); // 下载目录
    			$pics = string2array( $goodsList[$i]['slider_pics'] ); // 轮播图
    		}
    		
    		if (!empty($pics)) {
    			foreach ($pics as $item) {
    				get_image($item, $path);
    			}
    		}
    		
    	}
    	
    	// 下面的是配合前端做的动作，如果当数组下标大于等于货物总数(实际上数组下标等于总数减1就已经到数组最后一个了)
    	// 那么向前端发送一个over，让前端停止发送请求，如果没大于，则返回一个新的block让前端发送请求继续下载
    	$ajaxData = array();
    	if ($i >= $goodsCount) {
    		$ajaxData['over'] = true;
    	} else {
    		$ajaxData['block'] = $block + 1;
    	}
    	
    	$ajaxData['success'] = true;
    	$this->ajaxReturn($ajaxData);
    	
    }
}