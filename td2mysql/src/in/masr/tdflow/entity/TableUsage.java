package in.masr.tdflow.entity;

import in.masr.simplemysqldb.EntityColumn;
import in.masr.tdflow.helper.Transformer;

import java.math.BigDecimal;

public class TableUsage implements Formative {
	@EntityColumn
	public BigDecimal queryid;
	@EntityColumn
	public String db;
	@EntityColumn
	public String tb;
	@EntityColumn
	public String sessionid;

	@Override
	public void format() {
		db = Transformer.db(db);
		tb = Transformer.tb(tb);
	}
}
