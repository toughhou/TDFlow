<?php

//$_GET = array('cat' => 'graph', 'date' => '2013-05-04', 'whole' => '0', 'host' => 'mozart', 'type' => 'upstream', 'tables' => '["gdw_tables.dw_attr_detail_values"]', 'api' => 'data_flow');
//$_GET = array('api' => 'uc4_flow', 'cat' => 'th_graph');
//$_POST = array('jobs' => '["UC_TH_GBASE_UP_DONE"]', 'env' => 'td2');
//$_GET = array('api' => 'uc4_flow', 'cat' => 'check_lost_events', 'job' => 'UC_TH_HARMONY_UP_DONE', 'env' => 'td2');
//$_GET = array('api' => 'uc4_flow', 'cat' => 'touch_file_status_check');
//$_POST = array('dones' => '[{"env":"secondary","done_file":"dw_lstg.lstg_item_cndtn.done"},{"env":"secondary","done_file":"dw_lstg_item_w_done"},{"env":"secondary","done_file":"dw_user_info_load"},{"env":"secondary","done_file":"stg_items_freq_i.done"},{"env":"secondary","done_file":"dw_items_w_for_bid.done"},{"env":"secondary","done_file":"s_m_stg_items_arc_w--c_dw_win_ad_dw"},{"env":"primary","done_file":"dw_bid_ex_done"},{"env":"primary","done_file":"dw_ck_trans_ex_done"},{"env":"secondary","done_file":"dw_attr_detail_values_1_w_for_cndtn.done"},{"env":"secondary","done_file":"dw_lstg_item_w_for_attributes.done"},{"env":"secondary","done_file":"dw_attr.dw_attr_freq_batch.done"},{"env":"secondary","done_file":"dw_lstg_auct_end_dt_w.done"},{"env":"secondary","done_file":"dw_lstg_item_done"},{"env":"secondary","done_file":"dw_attr_lstg_dtl_w_for_cndtn.done"},{"env":"secondary","done_file":"dw_lstg_auct_end_dt.done"},{"env":"secondary","done_file":"dw_categ_leaf_prop.done"},{"env":"primary","done_file":"dw_dim.stg_categ_prop_w.extract.done"},{"env":"primary","done_file":"dw_dim.dw_mtdt_label_ftr.extract.done"},{"env":"secondary","done_file":"dw_lstg_item_desc.done"},{"env":"secondary","done_file":"dw_items_shipping_refresh.done"},{"env":"secondary","done_file":"dw_attr_detail_values_insert--dw_rtm_de_byr_mtrcs_dly_w"},{"env":"secondary","done_file":"dw_subsciption-C_DW_DP_WKLY_SLR_MAIN"},{"env":"secondary","done_file":"dw_user_host_done"},{"env":"secondary","done_file":"ab_dw_fdbk_dtl_slr_rtg_mtrc.done"},{"env":"secondary","done_file":"dw_find.stg_dw_find_prdt_itm_qlty_wk_w.done"},{"env":"secondary","done_file":"c_dw_attr_post_parser--c_dw_ddg_attr_w"},{"env":"secondary","done_file":"dw_ctlg_prod_lkp_dfe.done"},{"env":"secondary","done_file":"dw_ctlg_prod_lkp_freq_.done"},{"env":"secondary","done_file":"dw_ctlg_prod_lkp.done"},{"env":"secondary","done_file":"dw_lstg.lstg_item_vrtn_trait.tr.done"},{"env":"secondary","done_file":"dw_lstg.lstg_item_vrtn.tr.done"},{"env":"secondary","done_file":"dw_ctlg.ctlg_category_vcs.done"},{"env":"secondary","done_file":"dw_attr.attr_label.done"},{"env":"secondary","done_file":"dw_attr.attr_vcs_vers_map.done"},{"env":"secondary","done_file":"c_dw_find_item_cf_main"},{"env":"secondary","done_file":"dw_find.item_aspct_cf_i_freq.done"},{"env":"secondary","done_file":"ab_dw_fdbk_mtrc_prfl_lftm.done"},{"env":"secondary","done_file":"dw_user_info_paybox_info_upd_for_cdm"},{"env":"secondary","done_file":"dw_lstg.lstg_sale_tax.done"},{"env":"secondary","done_file":"dw_lstg_item_cold_done"},{"env":"secondary","done_file":"dw_attr.dw_attr_parse_tax_dtl_w.done"}]');

//$_GET = array('api' => 'flow', 'cat' => 'lkp_tables', 'term' => 'de', 'limit' => '10');
//$_GET=array('api'=>'uc4_flow','cat'=>'lkp_ths','term'=>'GBASE');

//$_GET = array('api' => 'flow', 'cat' => 'graph', 'date' => '2013-05-30', 'type' => 'upstream',  'table' => 'gdw_tables.dw_attr_detail');
//$_POST = array('tables' => '["prs_t.EBAY_TRANS_RLTD_EVENT"]');

//$_GET=array('api'=>'data_flow','cat'=>'graph','date'=>'2013-04-23','type'=>'upstream','host'=>'mozart','sessionid'=>'113303236');
//$_GET = array('api' => 'data_flow', 'cat' => 'data_ready', 'host' => 'mozart', 'tables' => '["gdw_tables.dw_attr_detail","gdw_tables.dw_attr_detail_values"]');

//exit();

define('FUNC', 'API_' . $_GET['api'] . '_' . $_GET['cat']);
require_once '../source/common.php';


/**********************************************************/
/***    @global @variable @definition   ** */
/**********************************************************/

$api = $_GET['api'] or ajax_ret(-1, 'API name required!');

$cat = $_GET['cat'] or ajax_ret(-1, 'API Category required');

extract($_GET);
extract($_POST);

/**********************************************************/
/***    @main @process @phase   ** */
/**********************************************************/

file_exists(S_ROOT . './api/' . $api . '.api.php') or ajax_ret(0, 'API name cannot found');

$con = $G['mysql_utility'];

require_once S_ROOT . './api/' . $api . '.api.php';

?>