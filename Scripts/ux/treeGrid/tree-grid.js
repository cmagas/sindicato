/*!
 * Ext JS Library 3.1.0
 * Copyright(c) 2006-2009 Ext JS, LLC
 * licensing@extjs.com
 * http://www.extjs.com/license
 */
Ext.onReady(function() {
    Ext.QuickTips.init();
	chkRow=new Ext.grid.CheckboxSelectionModel();
    var tree = new Ext.ux.tree.TreeGrid({
        title: 'Core Team Projects',
        width: 500,
        height: 300,
        renderTo: Ext.getBody(),
        enableDD: true,
		renderTo:'tblArbol',
        columns:[
				 chkRow,
				 {
            header: 'Task',
            dataIndex: 'task',
            width: 230,
			
        },{
            header: 'Duration',
            width: 100,
            dataIndex: 'duration',
            align: 'center',
            sortType: 'asFloat',
            tpl: new Ext.XTemplate('<select><option>Si</option><option>No</option></select>')
        },{
            header: 'Assigned To',
            width: 150,
            dataIndex: 'user'
        }],

        dataUrl: 'treegrid-data.json'
    });
});