Ext.ux.FCKeditor = Ext.extend
(Ext.Component,	{
 					 
					  constructor :	function( conf )
									{
										if(!conf.editor)
										{
											
											conf.editor	= new FCKeditor	(
																			conf.Name
																			,conf.Width
																			,conf.Height
																			,conf.ToolbarSet
																			,conf.Value
																		);
											if(conf.config!=undefined)
												conf.editor.Config["CustomConfigurationsPath"]=conf.config;
											
										}
										this.X=0;
										this.Y=0;
										if(conf.y !=undefined)
											this.Y=conf.y;
										if(conf.x =undefined)
											this.X=conf.x;
										
										
										Ext.apply(conf, conf.editor);
										
										Ext.apply(conf.Config,conf.fckConfig);
										
										//define/modify ext's component id
										conf.id = conf.Name;
										
										
										Ext.ux.FCKeditor.superclass.constructor.call(this, conf);			
										
										this.addEvents	(
															[
																/**
																 * @Event Function(editor)
																 * Fires after the size of the editor is changed. 
																 */
																'resize',
																
																/**
																 * @Event Function(editor , toolset)
																 * Fires after the toolset of the editor is changed.
																 */
																'toolSetChanged',
																
																
																/**
																 * @Event Function(editor)
																 * Fires after the editor is rendered.
																 */
																 'editorRender'
																 
															]
														);
									},
					  onRender: 	function(ct, position)
								  {
									  if(!this.tpl)
									  {
										  this.tpl = new Ext.XTemplate	(
																			  '<div class="x-window-mc" style="position:relative; top:'+this.Y+'px;left:'+this.X+'px">',this.CreateHtml(),'</div>'	
																		  );
									  }
									  if(position)
									  {
										  this.el = this.tpl.insertBefore(position,this ,true);
									  }
									  else
									  {
										  this.el = this.tpl.append(ct,this ,true);
									  }
								  },
					  
					  getInnerEditor : function()
									  {
										  return	Ext.ux.FCKeditorMgr.get(this.Name);
									  },
				
					  setToolbarSet : function(set)
									  {
										  var inner = this.getInnerEditor();			
										  if(inner)
											  this.Value = inner.GetData();
														  
										  this.ToolbarSet = set;
										  this.el.dom.innerHTML=this.CreateHtml();
										  this.fireEvent( 'toolSetChanged' , this, set);
									  },		
					  
					  setSize : 	function(size ,animate)
								  {
									  if(!this. rendered)
										  return ;
									  var domEl = this.getEditorFrame();
									  
									  if(domEl)
										  domEl.setSize(size.width ,size.height,animate);
						  
									  //var size =  this.getSize();
						  
									  this.Width = size.width;
									  this.Height = size.height;
									  
									  this.fireEvent( 'resize' , this);
								  },
					  
					  getSize : function()
					  {
						  return this.el.getSize();
					  },
					  
					  setHeight : function(h)
					  {
						  if(!this. rendered)
							  return ;
						  
						  var domEl = this.getEditorFrame();
						  
						  if(domEl)
							  domEl.setHeight(h);
						  
						  this.Height = this.getHeight();
						  
						  this.fireEvent( 'resize' , this);
					  },
					  
					  getHeight : function()
								  {
									  return  this.el.getHeight();
								  },
					  
					  setWidth : 	function(w)
								  {
									  if(!this. rendered)
										  return ;
									  
									  var domEl = this.getEditorFrame();
									  
									  if(domEl)
										  domEl.setWidth(w);
									  
									  this.Width = this.el.getWidth();
									  
									  this.fireEvent( 'resize' , this);
								  },
					  
					  getWidth : function()
								  {
									  return  this.el.getWidth();
								  },
					  
					  
					  setValue : 	function(html)
								  	{

									  	this.getInnerEditor().SetData(html);
								  	},
					 insertValue:	function(html)
					 				{

										this.getInnerEditor().InsertHtml(html);
									},
								  
					  getValue : 	function()
								  {
									  return this.getInnerEditor().GetData();
								  },
					  
					  //private
					  getEditorFrame : 	function()
										  {
											  if(!this. rendered)
												  return null;
												  
											  var dom =Ext.get(Ext.query("iframe",this.el.dom)[0]);
											  return Ext.get(dom);
										  },
					  
					  destroy: 	function()
								  {			
									  Ext.ux.FCKeditorMgr.remove(this.Name);			
									  Ext.ux.FCKeditor.superclass.destroy.call(this);
								  }
				  }
);
var editorColecciones;
Ext.ux.FCKeditorMgr = (	function()
						{
							var collections = new Object();
							return {
										register : function (name , o)
													{
														
														collections[name] = o;
														
														var editor = Ext.getCmp(name);	
														
														if(editor.ownerCt)
														{
															editor.ownerCt.doLayout();
														}
														editor.fireEvent('editorRender',editor);
													},
										remove : function(name)
										{
											delete collections[name];
										},
										get :function (name)
										{
											return collections[name];
										}
								};
						}
					)();

FCKeditor_OnComplete = typeof FCKeditor_OnComplete == 'function'?
FCKeditor_OnComplete.createSequence
(
	function( instance )
	{
		
		Ext.ux.FCKeditorMgr.register(instance.Name , instance);
	},
	FCKeditor_OnComplete
):
function( instance )
{
		Ext.ux.FCKeditorMgr.register(instance.Name , instance);
};
		
Ext.reg('fckeditor' , Ext.ux.FCKeditor);