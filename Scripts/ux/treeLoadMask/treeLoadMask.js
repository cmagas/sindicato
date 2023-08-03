Ext.namespace('Ext.ux.tree');
/**
 * A simple utility class for generically masking elements while loading tree data.  Specify the {@link #loader}
 * config option so that the masking will be automatically synchronized with the tree loader's loading
 * process and the mask element will be cached for reuse.  
 * <p>Example usage:</p>
 *<pre><code>
// An extended TreePanel:
Ext.ux.tree.TreePanel = Ext.extend(Ext.tree.TreePanel, {
    //a LoadMask config 
    loadMask: { msg: 'Loading...' }
    
    // private - extends the parent
    ,initEvents : function(){
        //call parent
        Ext.ux.tree.TreePanel.superclass.initEvents.call(this);
        
        if(this.loadMask){
            this._mask = new Ext.ux.tree.TreeLoadMask(this.bwrap,
                    Ext.apply({loader:this.loader}, this.loadMask));
        }
    }//eof initEvents
});
</code></pre>

 * @class Ext.ux.tree.TreeLoadMask
 * @constructor
 * Create a new TreeLoadMask
 * @param {Mixed} el The element or DOM node, or its id
 * @param {Object} config The config object
 */
Ext.ux.tree.TreeLoadMask = function(el, config){
    this.el = Ext.get(el);
    Ext.apply(this, config);
    
    //minimal delay so that the tree panel is layed out and the mask will be centered.
    this.loader.on('beforeload', this.onBeforeLoad, this, {delay:1});
    this.loader.on('load', this.onLoad, this);
    this.loader.on('loadexception', this.onLoad, this);
    this.removeMask = Ext.value(this.removeMask, false);
};

Ext.extend(Ext.ux.tree.TreeLoadMask, Ext.LoadMask, {
    /**
     * @cfg {Ext.tree.TreeLoader} loader
     * TreeLoader to which the mask is bound. The mask is displayed when a load request is issued, and
     * hidden on either load sucess, or load fail.
     */

    // private
    destroy : function(){
        this.loader.un('beforeload', this.onBeforeLoad, this);
        this.loader.un('load', this.onLoad, this);
        this.loader.un('loadexception', this.onLoad, this);
    } //eof destroy
    
    /**
     * @private
     * @param {Ext.tree.TreeLoader} l
     */
    ,onBeforeLoad: function(l){
        //Must check if the loader is still loading before displaying the mask. Otherwise if
        //we did not, we have a potential race-condition if the load completes before the 
        //mask is shown, which would result in the mask never being cleared.
        if (l.isLoading()){
            Ext.ux.tree.TreeLoadMask.superclass.onBeforeLoad.apply(this,arguments);
        }
    }//eof onBeforeLoad
});