<?php
/**
 * database adapter interface
 *
 * @version $Id$
 * @author purpen
 */
interface Anole_Dba_Adapter {
	/**
	 * open connect database
	 */
	function connect();
	/**
	 * close database connect
	 */
	function close();
	function query($sql,$size=-1,$page=1,$vars=array());
	function execute($sql,$vars=array());
	function genSeq($name);
	function dropSeq($name);
	function getFieldMetaList($table);
	/**
	 * 返回当前数据库的表名
	 * 
	 * @return array
	 */
	function getTableList();
}
/**vim:sw=4 et ts=4 **/
?>