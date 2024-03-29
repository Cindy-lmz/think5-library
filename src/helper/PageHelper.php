<?php
namespace library\helper;

use library\Helper;
use think\Db;
use think\db\Query;

/**
 *
 * Class PageHelper
 * @package library\helper
 */
class PageHelper extends Helper
{
    /**
     * 是否启用分页
     * @var boolean
     */
    protected $page;

    /**
     * 集合分页记录数
     * @var integer
     */
    protected $total;

    /**
     * 集合每页记录数
     * @var integer
     */
    protected $limit;

    /**
     * 是否渲染模板
     * @var boolean
     */
    protected $display;

    /**
     * 逻辑器初始化
     * @param string|Query $dbQuery
     * @param boolean $page 是否启用分页
     * @param boolean $display 是否渲染模板
     * @param boolean $total 集合分页记录数
     * @param integer $limit 集合每页记录数
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function init($dbQuery, $page = true, $display = true, $total = false, $limit = 0)
    {
        $this->page = $page;
        $this->total = $total;
        $this->limit = $limit;
        $this->display = $display;
        $this->query = $this->buildQuery($dbQuery);
        // 列表排序操作
        if ($this->controller->request->isPost()) $this->_sort();
        // 未配置 order 规则时自动按 sort 字段排序
        if (!$this->query->getOptions('order') && method_exists($this->query, 'getTableFields')) {
            if (in_array('sort', $this->query->getTableFields())) $this->query->order('sort desc');
        }
        // 列表分页及结果集处理
        if ($this->page) {
            // 分页每页显示记录数
            $limit = intval($this->controller->request->get('limit', cookie('page-limit')));
            cookie('page-limit', $limit = $limit >= 10 ? $limit : 20);
            if ($this->limit > 0) $limit = $this->limit;
            $rows = [];
            $page = $this->query->paginate($limit, $this->total, ['query' => ($query = $this->controller->request->get())]);
            foreach ([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 110, 120, 130, 140, 150, 160, 170, 180, 190, 200] as $num) {
                list($query['limit'], $query['page'], $selected) = [$num, '1', $limit === $num ? 'selected' : ''];
                $url = url('@admin') . '#' . $this->controller->request->baseUrl() . '?' . urldecode(http_build_query($query));
                array_push($rows, "<option data-url='{$url}' " . ($rows === $num ? 'selected' : '') . " value='{$num}' {$selected}>{$num}</option>");
            }
            $selects = "<select data-auto-none>" . join('', $rows) . "</select>";
            $pagetext = "<div class='pagination-container nowrap'><span>共 {$page->total()} 条记录，每页显示 {$selects} 条，共 {$page->lastPage()} 页当前显示第 {$page->currentPage()} 页。</span>{$page->render()}</div>";
            $pagehtml = "<div class='pagination-container nowrap'><span>{$pagetext}</span></div>";
            $this->controller->assign('pagehtml', preg_replace('|href="(.*?)"|', 'data-open="$1" onclick="return false" href="$1"', $pagehtml));
            $result = ['page' => ['limit' => intval($limit), 'total' => intval($page->total()), 'pages' => intval($page->lastPage()), 'current' => intval($page->currentPage())], 'list' => $page->items()];
        } else {
            $result = ['list' => $this->query->select()];
        }
        if (false !== $this->controller->callback('_page_filter', $result['list']) && $this->display) {
            return $this->controller->fetch('', $result);
        } else {
            return $result;
        }
    }

    /**
     * 列表排序操作
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    protected function _sort()
    {
        switch (strtolower($this->controller->request->post('action', ''))) {
            case 'resort':
                foreach ($this->controller->request->post() as $key => $value) {
                    if (preg_match('/^_\d{1,}$/', $key) && preg_match('/^\d{1,}$/', $value)) {
                        list($where, $update) = [['id' => trim($key, '_')], ['sort' => $value]];
                        if (false === Db::table($this->query->getTable())->where($where)->update($update)) {
                            return $this->controller->error(lang('think_library_sort_error'));
                        }
                    }
                }
                return $this->controller->success(lang('think_library_sort_success'), '');
            case 'sort':
                $where = $this->controller->request->post();
                $sort = intval($this->controller->request->post('sort'));
                unset($where['action'], $where['sort']);
                if (Db::table($this->query->getTable())->where($where)->update(['sort' => $sort]) !== false) {
                    return $this->controller->success(lang('think_library_sort_success'), '');
                } else {
                    return $this->controller->error(lang('think_library_sort_error'));
                }
        }
    }

}