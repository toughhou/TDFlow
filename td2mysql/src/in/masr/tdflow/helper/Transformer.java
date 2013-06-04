package in.masr.tdflow.helper;

public class Transformer {

	public static String userName(String username) {
		if (username == null)
			return null;
		return username.trim().toUpperCase();
	}

	public static String db(String db) {
		if (db == null)
			return null;

		return db.trim().toLowerCase().replace("\"", "");
	}

	public static String host(String system) {
		if (system == null)
			return null;

		return system.trim().toLowerCase().replace("\"", "");
	}

	public static String col(String col) {
		if (col == null)
			return null;

		return col.trim().toLowerCase().replace("\"", "");
	}

	public static String tb(String table) {
		if (table == null)
			return null;

		return table.trim().toUpperCase().replace("\"", "");
	}

}
