package in.masr.tdflow;

import in.masr.masrutils.Config;

public class CFG extends Config {

	public static void init() {
		setDefaltValue("teradata_fetch_size", "4000");
		setDefaltValue("mysql_insert_buffer_size", "500");
		setDefaltValue("mysql_port", "3306");
		setDefaltValue("flow_expire", "45");
		setDefaltValue("fetch_day_ago", "1");
		setDefaltValue("td2mysql_folder", "td2mysql");
		setDefaltValue("web_folder", "web");
	}
}
