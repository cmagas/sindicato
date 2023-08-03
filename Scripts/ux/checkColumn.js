Ext.grid.CheckColumn = function(config)
{
    Ext.apply(this, config);
	
    if(!this.id){
        this.id = Ext.id();
    }
    this.renderer = this.renderer.createDelegate(this);
};

Ext.grid.CheckColumn.prototype =
{
    init : function(grid)
	{
        this.grid = grid;
        this.grid.on('render', function(){
            var view = this.grid.getView();
            view.mainBody.on('mousedown', this.onMouseDown, this);
        }, this);
    },

    onMouseDown : function(e, t)
	{
		if(t.className && t.className.indexOf('x-grid3-cc-'+this.id) != -1)
		{
            e.stopEvent();
            var index = this.grid.getView().findRowIndex(t);
            var record = this.grid.store.getAt(index);
			if(typeof(record.data[this.dataIndex])!='boolean')
			{
				if(record.data[this.dataIndex]=='1')
					record.data[this.dataIndex]=true;
				else			
					record.data[this.dataIndex]=false;
			}
            var editEvent = 
			{
            	grid: this.grid,
            	record: this.grid.store.getAt(index),
            	field: this.dataIndex,
            	value: !record.data[this.dataIndex],
            	originalValue: record.data[this.dataIndex],
            	row: index,
            	column: this.grid.getColumnModel().findColumnIndex(this.dataIndex)
            };
				
			var editBeforeEvent	=	{
										grid: this.grid,
										record: this.grid.store.getAt(index),
										field: this.dataIndex,
										value: record.data[this.dataIndex],
										row: index,
										column: this.grid.getColumnModel().findColumnIndex(this.dataIndex),
										cancel:false
									}	
			
			this.grid.fireEvent('beforeedit',editBeforeEvent);
			if(!editBeforeEvent.cancel)
			{
				record.set(this.dataIndex, editEvent.value);
				this.grid.fireEvent('afteredit',editEvent);
			}
			
        }
    },

    renderer : function(v, p, record)
	{
        p.css += ' x-grid3-check-col-td'; 
		var valor=v;
		if(typeof(valor)!='boolean')
			if(v=='1')
				valor=true;
			else
				valor=false;
        return '<div class="x-grid3-check-col'+(valor?'-on':'')+' x-grid3-cc-'+this.id+'">&#160;</div>';
    }
};