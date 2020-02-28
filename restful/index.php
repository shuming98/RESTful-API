<?php
require_once __DIR__ . '/../lib/User.php';
require_once __DIR__ . '/../lib/Article.php';
$pdo = require_once __DIR__ . '/../lib/db.php';

/**
 * ——————————属性——————————
 * api对象：$_user $_article
 * 请求：$_requestMethod $_resourceName $_id
 * 允许：$_allowRequestMethod $_allowResource
 * 状态码：$_statusCodes
 * ——————————方法——————————
 * 入口：__construct() run()
 * 请求：_setupRequestMethod() _setupResource() _setupId()
 * 输出：_json()
 * 资源：handleUser() handleArticle()
 * 获取请求参数：_getBodyParams()
 * 用户登陆：_userLogin()
 * 文章处理：_handleArticleCreate() _handleArticleEdit() _handleArticleDelete() _handleArticleList() _handleArticleView()
 */
class Restful
{
	private $_user;
	private $_article;

	/**
	 * 请求资源的方法
	 * @var [type]
	 */
	private $_requestMethod;

	/**
	 * 请求资源的名称
	 * @var [type]
	 */
	private $_resourceName;

	/**
	 * 请求资源的id
	 * @var [type]
	 */
	private $_id;

	/**
	 * 允许请求的资源
	 * @var [type]
	 */
	private $_allowResource = ['users','articles'];

	/**
	 * 允许请求的HTTP方法
	 * @var [type]
	 */
	private $_allowRequestMethods = ['GET','POST','PUT','DELETE','OPTIONS'];

	/**
	 * 常用的状态码
	 * @var [type]
	 */
	private $_statusCodes = [
		200 => 'Ok',
		204 => 'No Content',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		500 => 'Server Internal Error',
	];

	/**
	 * Restful 构造方法
	 * @param [type] $_user    [description]
	 * @param [type] $_article [description]
	 */
	public function __construct($_user,$_article)
	{
		$this->_user = $_user;
		$this->_article = $_article;
	}

	public function run()
	{
		try{
			$this->_setupRequestMethod();
			$this->_setupResource();
			if($this->_resourceName == 'users'){
				$this->_json($this->_handleUser());
			}else{
				$this->_json($this->_handleArticle());
			}
		}catch (Exception $e){
			$this->_json(['error' => $e->getMessage()],$e->getCode());
		}

	}

	/**
	 * 初始化请求方法
	 * @return [type] [description]
	 */
	private function _setupRequestMethod()
	{
		$this->_requestMethod = $_SERVER['REQUEST_METHOD'];
		if(!in_array($this->_requestMethod,$this->_allowRequestMethods)){
			throw new Exception("请求方法不被允许", 405);
		}
	}

	/**
	 * 初始化请求资源
	 * URI:第一个参数为请求的资源，第二个参数为请求的id
	 * @return [type] [description]
	 */
	private function _setupResource()
	{
		$path = $_SERVER['PATH_INFO'];
		$param = explode('/',$path);
		$this->_resourceName = $param[1];
		if(!in_array($this->_resourceName, $this->_allowResource)){
			throw new Exception("请求资源不被允许",400);
		}
		if(!empty($param[2])){
			$this->_id = $param[2];
		}
	}

	/**
	 * 初始化请求id
	 * 上一个函数已经实现了
	 */
	private function _setupId()
	{

	}

	/**
	 * 输出JSON
	 * @param  [type]  $array [description]
	 * @param  integer $code  [description]
	 * @return [type]         [description]
	 */
	private function _json($array,$code = 0)
	{
		if($array === null && $code === 0){
			$code = 204;
		}
		if($array !== null && $code === 0){
			$code = 200;
		}
		//if($code > 0 && $code != 200 && $code != 204 ){
		header("HTTP/1.1 " . $code . " " . $this->_statusCodes[$code]);
		header('Content-Type:application/json;charset=utf-8');
		if($array !== null){
			echo json_encode($array,JSON_UNESCAPED_UNICODE);
		}
		exit();
	}

	/**
	 * 请求用户资源
	 * @return [type] [description]
	 */
	private function _handleUser()
	{
		if($this->_requestMethod != 'POST'){
			throw new Exception("请求方法不被允许", 405);
		}

		$body = $this->_getBodyParams();
		if(empty($body['username'])){
			throw new Exception("用户名不能为空", 400);
		}
		if(empty($body['password'])){
			throw new Exception("密码不能为空", 400);
		}
		return $this->_user->register($body['username'],$body['password']);


	}

	/**
	 * 请求文章资源
	 * @return [type] [description]
	 */
	private function _handleArticle()
	{
		switch($this->_requestMethod){
			case 'POST':
				return $this->_handleArticleCreate();
			case 'PUT':
				return $this->_handleArticleEdit();
			case 'DELETE':
				return $this->_handleArticleDelete();
			case 'GET':
				if(empty($this->_id)){
					return $this->_handleArticleList();
				}else{
					return $this->_handleArticleView();
				}
			default:
				throw new Exception("请求方法不被允许", 405);
		}

	}

	/**
	 * 文章创建
	 * @return [type] [description]
	 */
	private function _handleArticleCreate()
	{
		$body = $this->_getBodyParams();
		if(empty($body['title'])){
			throw new Exception("文章标题不能为空",400);
		}
		if(empty($body['content'])){
			throw new Exception("文章内容不能为空",400);
		}

		$user = $this->_userLogin($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);

		try{
			$article = $this->_article->create($body['title'],$body['content'],$user['userId']);
			return $article;
		}catch(Exception $e){
			if(in_array($e->getCode(),
			[
				ErrorCode::ARTICLE_TITLE_CANNOT_EMPTY,
				ErrorCode::ARTICLE_CONTENT_CANNOT_EMPTY
			])){
				throw new Exception($e->getMessage(), 400);
			}
			throw new Exception($e->getMessage(), 500);
		}

	}

	/**
	 * 文章编辑
	 * @return [type] [description]
	 */
	private function _handleArticleEdit()
	{
		$user = $this->_userLogin($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);
		try{
			$article = $this->_article->view($this->_id);
			if($article['userId'] !== $user['userId']){
				throw new Exception("你无权编辑", 403);
			}
			$body = $this->_getBodyParams();
			$title = empty($body['title']) ? $article['title'] : $body['title'];
			$content = empty($body['content']) ? $article['content'] : $body['content'];
			if($title == $article['title'] && $content == $article['content']){
				return $article;
			}
			return $this->_article->edit($article['articleId'],$title,$content,$user['userId']);
		} catch (Exception $e){
			if($e->getCode() < 100){
				if($e->getCode() == ErrorCode::ARTICLE_NOT_FOUND){
					throw new Exception($e->getMessage(), 404);
				}else{
					throw new Exception($e->getMessage(), 400);
				}
			}else{
				throw $e;
			}
		}
	}

	/**
	 * 文章删除
	 * @return [type] [description]
	 */
	private function _handleArticleDelete()
	{
		$user = $this->_userLogin($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);
		try{
			$article = $this->_article->view($this->_id);
			if($article['userId'] !== $user['userId']){
				throw new Exception("你无权编辑", 403);
			}
			$this->_article->delete($article['articleId'],$user['userId']);
			return null;
		}catch(Exception $e){
			if($e->getCode() < 100){
				if($e->getCode() == ErrorCode::ARTICLE_NOT_FOUND){
					throw new Exception($e->getMessage(), 404);
				}else{
					throw new Exception($e->getMessage(), 400);
				}
			}else{
				return $e;
			}
		}
	}

	/**
	 * 文章列表
	 * @return [type] [description]
	 */
	private function _handleArticleList()
	{
		$user = $this->_userLogin($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);
		$page = isset($_GET['page']) ? $_GET['page'] : 1;
		$size = isset($_GET['page']) ? $_GET['size'] : 10;
		if($size > 100){
			throw new Exception("分页大小最大为100", 400);
		}
		return $this->_article->getList($user['userId'],$page,$size);
	}

	/**
	 * 查看文章
	 * @return [type] [description]
	 */
	private function _handleArticleView()
	{
		try{
			return $this->_article->view($this->_id);
		}catch(Exception $e){
			if($e->getCode() == ErrorCode::ARTICLE_NOT_FOUND){
				throw new Exception($e->getMessage(), 404);
			}else{
				throw new Exception($e->getMessage(), 500);
			}
		}
	}

	/**
	 * 用户登录
	 * @param  [type] $PHP_AUTH_USER [description]
	 * @param  [type] $PHP_AUTH_PW   [description]
	 * @return [type]                [description]
	 */
	private function _userLogin($PHP_AUTH_USER,$PHP_AUTH_PW)
	{
		try{
			return $this->_user->login($PHP_AUTH_USER,$PHP_AUTH_PW);
		}catch(Exception $e){
			if(in_array($e->getCode(),
			[
				ErrorCode::USERNAME_CANNOT_EMPTY,
				ErrorCode::PASSWORD_CANNOT_EMPTY,
				ErrorCode::USERNAME_OR_PASSWORD_INVALID
			])){
				throw new Exception($e->getMessage(), 400);
			}
		}
	}


	/**
	 * 获取请求体参数
	 * @return [type] [description]
	 */
	private function _getBodyParams()
	{
		$raw = file_get_contents('php://input');
		if(empty($raw)){
			throw new Exception("请求参数错误", 400);
		}
		return json_decode($raw,true);
	}
}

$user = new User($pdo);
$article = new Article($pdo);

$restful = new Restful($user,$article);
$restful->run();