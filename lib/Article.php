<?php

require_once __DIR__ . '/ErrorCode.php';

class Article
{
	/**
	 * $_db 数据库句柄
	 * @var [type]
	 */
	private $_db;

	/**
	 * 构造方法
	 * @param [type] $_db 数据库连接
	 */
	public function __construct($_db)
	{
		$this->_db = $_db;
	}

	/**
	 * 创建文章
	 * @param  [type] $title   [description]
	 * @param  [type] $content [description]
	 * @param  [type] $userId  [description]
	 * @return array
	 */
	public function create($title,$content,$userId)
	{
		if(empty($title)){
			throw new Exception("文章标题不能为空", ErrorCode::ARTICLE_TITLE_CANNOT_EMPTY);
		}
		if(empty($content)){
			throw new Exception("文章内容不能为空", ErrorCode::ARTICLE_CONTENT_CANNOT_EMPTY);
		}
		$sql = 'insert into `article`(`title`,`content`,`userId`,`createdAt`) values(:title,:content,:userId,:createdAt)';
		$stmt = $this->_db->prepare($sql);
		$createdAt = date('Y-m-d',time());
		$stmt->bindParam(':title',$title);
		$stmt->bindParam(':content',$content);
		$stmt->bindParam(':userId',$userId);
		$stmt->bindParam(':createdAt',$createdAt);
		if(!$stmt->execute()){
			throw new Exception("创建文章失败", ErrorCode::ARTICLE_CREATE_FAILED);
		}
		return [
			'articleId'=>$this->_db->lastInsertId(),
			'userId'=>$userId,
			'title'=>$title,
			'content'=>$content,
			'createdAt'=>$createdAt
		];
	}

	/**
	 * 查看一篇文章
	 * @param  [type] $articleId [description]
	 * @return array
	 */
	public function view($articleId)
	{
		if(empty($articleId)){
			throw new Exception("文章Id不能为空", ErrorCode::ARTICLEID_CANNOT_EMPTY);
		}
		$sql = 'select * from `article` where `articleId`=:articleId';
		$stmt = $this->_db->prepare($sql);
		$stmt->bindParam(':articleId',$articleId);
		$stmt->execute();
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		if(empty($res)){
			throw new Exception("文章不存在",ErrorCode::ARTICLE_NOT_FOUND);
		}
		return $res;
	}

	/**
	 * 编辑文章
	 * @param  [type] $articleId [description]
	 * @param  [type] $title     [description]
	 * @param  [type] $content   [description]
	 * @param  [type] $userId    [description]
	 * @return  array
	 **/
	public function edit($articleId,$title,$content,$userId)
	{
		$article = $this->view($articleId);
		if($userId !== $article['userId']){
			throw new Exception("你无权编辑该文章", ErrorCode::PERMISSION_DENIED);
		}
		$title = empty($title)?article['title']:$title;
		$content = empty($content)?article['content']:$content;
		if($article['title'] === $title && $article['content'] === $content){
			return $article;
		}
		$sql = 'update `article` set `title`=:title,`content`=:content where `articleId`=:articleId';
		$stmt = $this->_db->prepare($sql);
		$stmt->bindParam(':articleId',$articleId);
		$stmt->bindParam(':title',$title);
		$stmt->bindParam(':content',$content);
		if(!$stmt->execute()){
			throw new Exception("编辑文章失败", ErrorCode::ARTICLE_EDIT_FAILED);
		}
		return [
			'articleId'=>$articleId,
			'userId'=>$userId,
			'title'=>$title,
			'content'=>$content,
			'createdAt'=>$article['createdAt']
		];
	}

	/**
	 * 删除文章
	 * @param  [type] $articleId [description]
	 * @param  [type] $userId    [description]
	 * @return [type]            [description]
	 */
	public function delete($articleId,$userId)
	{
		$article = $this->view($articleId);
		if($userId !== $article['userId']){
			throw new Exception("你无权编辑该文章", ErrorCode::PERMISSION_DENIED);
		}
		$sql = 'delete from `article` where `articleId` = :articleId and `userId` = :userId';
		$stmt = $this->_db->prepare($sql);
		$stmt->bindParam(':articleId',$articleId);
		$stmt->bindParam(':userId',$userId);
		if(!$stmt->execute()){
			throw new Exception("删除文章失败", ErrorCode::ARTICLE_DELETE_FAILED);
		}
		return true;
	}

	/**
	 * 读取文章列表
	 * @param  [type]  $userId [description]
	 * @param  integer $page   [description]
	 * @param  integer $size   [description]
	 * @return array
	 */
	public function getList($userId,$page=1,$size=10)
	{
		if($size>100){
			throw new Exception("分页大小最大为100",ErrorCode::PAGE_SIZE_TO_BIG);
		}
		$sql = 'select * from `article` where `userId`=:userId limit :limit,:offset';
		$limit = ($page-1)*$size;
		$limit = $limit < 0 ? 0 : $limit;
		$stmt = $this->_db->prepare($sql);
		$stmt->bindParam(':userId',$userId);
		$stmt->bindParam(':limit',$limit);
		$stmt->bindParam(':offset',$size);
		$stmt->execute();
		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}
}