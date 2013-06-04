package in.masr.tdflow;

import in.masr.masrutils.FileOperation;
import in.masr.masrutils.Logger;
import in.masr.reonlyeradb.ReonlyTeraDB;
import in.masr.simplemysqldb.DBUtils;
import in.masr.simplemysqldb.SimpleMysqlDB;
import in.masr.tdflow.entity.SQLRecord;
import in.masr.tdflow.entity.TableUsage;

import java.io.File;
import java.sql.ResultSet;

public class Fetcher {
	private SimpleMysqlDB mysqlDB;
	private ReonlyTeraDB teraDB;
	private FileOperation fileOps;
	private static String BASE_FOLDER = "";

	public Fetcher() throws Exception {
		Logger.out("SQL Fetcher Started...");
		mysqlDB = new SimpleMysqlDB(CFG.s("mysql_host"), CFG.s("mysql_user"),
				CFG.s("mysql_passwd"), CFG.s("mysql_database"));
		mysqlDB.setPort(CFG.i("mysql_port"));
		mysqlDB.setInsertBufferSize(CFG.i("mysql_insert_buffer_size"));
		mysqlDB.connect();
		teraDB = new ReonlyTeraDB(CFG.s("teradata_host"),
				CFG.s("teradata_user"), CFG.s("teradata_passwd"));
		teraDB.setFetchSize(CFG.i("teradata_fetch_size"));
		teraDB.connect();
		fileOps = new FileOperation(BASE_FOLDER);
	}

	private void loadFlowSQL(int ago) throws Exception {
		String queyrtext = fileOps.getContent("sql/fetch_dbc_dbqllogtbl.sql");
		ResultSet sqlSet = teraDB.getResultSetByTemplateSQL(queyrtext,
				new String[] { "" + ago });

		mysqlDB.executeSQL("delete from flow_dbql_raw");
		int bid = mysqlDB.startInsertBatch("flow_dbql_raw", SQLRecord.class);

		while (sqlSet.next()) {
			SQLRecord sqlRecord = new SQLRecord();
			DBUtils.loadData(sqlSet, SQLRecord.class, sqlRecord);
			sqlRecord.format();
			mysqlDB.insert(sqlRecord, bid);
		}
		sqlSet.close();

		mysqlDB.endInsertBatch(bid);

	}

	private void loadFlowObjUsg(int ago) throws Exception {
		String queyrtext = fileOps.getContent("sql/fetch_dbc_dbqlobjtbl.sql");
		ResultSet tbuSet = teraDB.getResultSetByTemplateSQL(queyrtext,
				new String[] { "" + ago });
		mysqlDB.executeSQL("delete from flow_obj_usg_raw");

		int bid = mysqlDB
				.startInsertBatch("flow_obj_usg_raw", TableUsage.class);
		while (tbuSet.next()) {
			TableUsage objUsage = new TableUsage();
			DBUtils.loadData(tbuSet, TableUsage.class, objUsage);
			objUsage.format();
			mysqlDB.insert(objUsage, bid);
		}
		tbuSet.close();
		mysqlDB.endInsertBatch(bid);
	}

	public static void main(String args[]) throws Exception {
		CFG.init();
		String td2mysql = CFG.s("td2mysql_folder");
		BASE_FOLDER = CFG.HOME + File.separator + td2mysql;
		Logger.setLogFolderAbsolutePath(BASE_FOLDER + File.separator + "log");
		Logger.init();

		Fetcher fetcher = new Fetcher();
		int fetchDayAgo = CFG.i("fetch_day_ago");

		Logger.out("Start fetching dbc.DBQLogTbl...");
		fetcher.loadFlowSQL(CFG.i("fetch_day_ago"));
		Logger.out("Ended fetching dbc.DBQLogTbl...");

		Logger.out("Start fetching dbc.DBQLObjTbl...");
		fetcher.loadFlowObjUsg(fetchDayAgo);
		Logger.out("Ended fetching dbc.DBQLObjTbl...");
	}
}
