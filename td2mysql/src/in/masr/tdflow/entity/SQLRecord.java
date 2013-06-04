package in.masr.tdflow.entity;

import in.masr.simplemysqldb.EntityColumn;
import in.masr.tdflow.helper.Transformer;

import java.math.BigDecimal;
import java.sql.Date;

public class SQLRecord implements Formative {

	@EntityColumn
	public BigDecimal sessionid;
	@EntityColumn
	public BigDecimal queryid;
	@EntityColumn
	public String username;
	@EntityColumn
	public Date acctstringdate;
	@EntityColumn
	public String starttime;
	@EntityColumn
	public String lastresptime;
	@EntityColumn
	public String querytext;
	@EntityColumn
	public float myeffectivecpu;
	@EntityColumn
	public float totaliocount;

	@Override
	public void format() {
		username = Transformer.userName(username);
	}

}
