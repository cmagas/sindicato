Ext.namespace('Ext.ux.menu');

Ext.ux.menu.Menu = Ext.extend(Ext.menu.Menu, {
	initComponent: function() {
		Ext.ux.menu.Menu.superclass.initComponent.apply(this, arguments);
		if (Ext.ux.ManagedIframe) {
			this.on('beforeshow', function() {
				Ext.ux.ManagedIFrame.Manager.showShims();
			}, Ext.ux.ManagedIFrame.Manager);
			this.on('beforehide', function() {
				Ext.ux.ManagedIFrame.Manager.hideShims();
				return true;
			}, Ext.ux.ManagedIFrame.Manager);
		}
		this.on('itemclick', function() {
			this.hide();
		});
	},
	onRender: function(ct, position) {
                Ext.ux.menu.Menu.superclass.onRender.call(this, ct, position);
		this.getEl().on('contextmenu', function(e) {
			e.stopEvent();
			return false;
		});
	}
});
Ext.reg('ux-menu', Ext.ux.menu.Menu);