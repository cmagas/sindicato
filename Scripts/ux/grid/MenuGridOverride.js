/**********
*
* http://TDG-i.com TDG innovations, LLC
* 03/03/2008, Jay Garcia, jgarcia@tdg-i.com
* Column Views Menu Overrides.  
* 
* Purpose : Adds 'Show All Columns' to Columns menu, reducing # of clicks to show all.
*           Adds 'Change View' Menu, which allows you to customize the views  according to the grid.gridViews property.
*
* Works with EXT 2.0+
*
* License :  Free to use, just please don't sell.  
*            This was made for myself, my customers and the EXT Community.
*
* Waranty : None.  I can answer questions via email: jgarcia@tdg-i.com
*
**********/
Ext.override(Ext.grid.GridView, {
    renderUI : function(){

        var header = this.renderHeaders();
        var body = this.templates.body.apply({rows:''});


        var html = this.templates.master.apply({

            body: body,
            header: header
        });

        var g = this.grid;

        g.getGridEl().dom.innerHTML = html;

        this.initElements();


        this.mainBody.dom.innerHTML = this.renderRows();

        this.processRows(0, true);


        // get mousedowns early
        Ext.fly(this.innerHd).on("click", this.handleHdDown, this);
        this.mainHd.on("mouseover", this.handleHdOver, this);

        this.mainHd.on("mouseout", this.handleHdOut, this);
        this.mainHd.on("mousemove", this.handleHdMove, this);

        this.scroller.on('scroll', this.syncScroll,  this);

        if(g.enableColumnResize !== false){
            this.splitone = new Ext.grid.GridView.SplitDragZone(g, this.mainHd.dom);
        }

        if(g.enableColumnMove){
            this.columnDrag = new Ext.grid.GridView.ColumnDragZone(g, this.innerHd);

            this.columnDrop = new Ext.grid.HeaderDropZone(g, this.mainHd.dom);
        }

        if(g.enableHdMenu !== false){
            if(g.enableColumnHide !== false){
				this.colMenu = new Ext.menu.Menu({id:g.id + "-hcols-menu"});
                this.colMenu.on("beforeshow", this.beforeColMenuShow, this);
                this.colMenu.on("itemclick", this.handleHdMenuClick, this);
            }
            this.hmenu = new Ext.menu.Menu({id: g.id + "-hctx"});

            this.hmenu.add(
                {id:"asc", text: this.sortAscText, cls: "xg-hmenu-sort-asc"},
                {id:"desc", text: this.sortDescText, cls: "xg-hmenu-sort-desc"}

            );
			
			if(g.enableColumnHide !== false){
                this.hmenu.add('-',
                    {id:"columns", text: this.columnsText, menu: this.colMenu, iconCls: 'x-cols-icon'}
                );
            }
			
			/**********
			*
			* http://TDG-i.com TDG innovations, LLC
			* http://tdg-i.com
			* Column Views Menu
			*
			**********/
			if (g.columnViews) {
				this.colViewMenu = new Ext.menu.Menu({ id : g.id + "-colview-menu"});

                this.colViewMenu.on("beforeshow", this.beforeColViewsMenuShow, this);
				
				this.hmenu.add('-', { 
						id		: "columnViews", 
						text	: g.columnViewText || 'Change View' , 
						menu	: this.colViewMenu, 
						iconCls : 'tdgi-extgrid-colview-icon'
					}
				);
			}
		
            this.hmenu.on("itemclick", this.handleHdMenuClick, this);
        }

        if(g.enableDragDrop || g.enableDrag){
            var dd = new Ext.grid.GridDragZone(g, {
                ddGroup : g.ddGroup || 'GridDD'
            });
        }

        this.updateHeaderSortState();
    },

	
    // private
    beforeColMenuShow : function(){
        var cm = this.cm,  colCount = cm.getColumnCount();
        this.colMenu.removeAll();

		/**********
		*
		* http://TDG-i.com TDG innovations, LLC
		* http://tdg-i.com
		* Show All Columns menu.  Injected before 'Columns' menu.
		*
		**********/	
		this.colMenu.add({
			text 	: 'Show All',
			handler : this.showAllColumns,
			iconCls : 'tdgi-extgrid-showall-icon',
			scope 	: this
		}, '-');
		
		//Below is EXT's code.
        for(var i = 0; i < colCount; i++){
            if(cm.config[i].fixed !== true && cm.config[i].hideable !== false){
                this.colMenu.add(new Ext.menu.CheckItem({
                    id: "col-"+cm.getColumnId(i),
                    text: cm.getColumnHeader(i),
                    checked: !cm.isHidden(i),
                    hideOnClick:false,
                    disabled: cm.config[i].hideable === false
                }));
            }
        }
    },	
	/**********
	*
	* http://TDG-i.com TDG innovations, LLC
	* http://tdg-i.com
	* Lazy Rendering of grid views submenu. Similar to modified method above
	*
	**********/
    beforeColViewsMenuShow : function(){
		if (this.colViewMenu.items.items.length == 0) {

			var grid = this.grid;
			var	cm	  = this.cm;
			var cv = grid.columnViews;
			var colViewCount = grid.columnViews.length;			
			for(var i = 0; i < colViewCount; i++){
				var view = cv[i];

				this.colViewMenu.add(new Ext.menu.Item({
					id			: "cvm-" + cm.getColumnId(i),
					text		: view.text  || 'undefined!',
					columnView	: view, 
					iconCls		: view.iconCls || 'tdgi-extgrid-filterview-default',
					scope		: this,
					handler		: this.filterColumns
				}));

			}
		}
	
	},
	/**********
	*
	* http://TDG-i.com TDG innovations, LLC
	* http://tdg-i.com
	* Shows all columns via the menu added 
	*
	**********/
	showAllColumns : function() {
		function showColumn(column, colIndex, allColumns) {
			this.cm.setHidden(colIndex, false);
		}
		Ext.each(this.cm.config, showColumn, this);
	},
	/**********
	*
	* http://TDG-i.com TDG innovations, LLC
	* http://tdg-i.com
	* Filters, called by MenuItem
	*
	**********/	
	filterColumns : function(menuItem) {

		var cvCols	= menuItem.columnView.columns;
		var grid 		= this.grid;
		var cm	  		= this.cm;
		var cv 			= grid.columnViews;
		
		function doFilter(column, colIndex, allColumns) {
			var hidden = true;			
			
			for(var i = 0; i < cvCols.length; i++){
				var cvColumn = cvCols[i]; 
				if (cvColumn  == column.dataIndex) {
					hidden = false;
				}
			}
			
			cm.setHidden(colIndex, hidden);			
		
		}
		Ext.each(cm.config, doFilter, this);
	}
});

