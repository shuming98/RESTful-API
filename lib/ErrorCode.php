<?php
class ErrorCode
{
	const USERNAME_CANNOT_EMPTY = 1;//用户名不能为空
	const PASSWORD_CANNOT_EMPTY = 2;//密码不能为空
	const USERNAME_EXISTS = 3;//用户已存在
	const REGISTER_FAIL = 4;//用户注册失败
	const USERNAME_OR_PASSWORD_INVALID = 5;//用户注册失败

	const ARTICLE_TITLE_CANNOT_EMPTY = 6;//文章标题不能为空
	const ARTICLE_CONTENT_CANNOT_EMPTY = 7;//文章内容不能为空
	const ARTICLE_CREATE_FAILED = 8;//文章创建失败
	const ARTICLEID_CANNOT_EMPTY = 9;//文章id 不能为空
	const ARTICLE_NOT_FOUND = 10;//文章不存在
	const PERMISSION_DENIED = 11;//权限不够
	const ARTICLE_EDIT_FAILED = 12;//文章编辑失败
	const ARTICLE_DELETE_FAILED = 13;//文章删除失败
	const PAGE_SIZE_TO_BIG = 14;//分页数量太大
	const SERVER_INTERNAL_ERROR = 15;//服务器内部错误
}
 ?>
