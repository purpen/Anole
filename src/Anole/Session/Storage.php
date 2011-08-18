<?php
/**
 * Session存储层接口
 * 
 * 用于实现将sesion数据实际存储并持久化
 * 
 * @version $Id$
 * @author purpen
 */
interface Anole_Session_Storage {
	/**
     * 初始化storage,读取session数据
     *
     * @return array
     */
    function init();
    /**
     * 刷新session数据并持久化到后端容器中
     *
     * @param array
     */
    function store($data);
}
/** vim:sw=4 et ts=4 **/
?>