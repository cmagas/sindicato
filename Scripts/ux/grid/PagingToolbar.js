Ext.PagingToolbar.override(	{
								hideRefresh:false,
								onFirstLayout:Ext.emptyFn,
								onLoad:	function(store,r,o)
										  {
											  if(!this.rendered)
											  {
												  this.dsLoaded=[store,r,o];return;
											  }
											  var p=this.getParams();
											  this.cursor=(o.params&&o.params[p.start])?o.params[p.start]:0;
											  var d=this.getPageData(),ap=d.activePage,ps=d.pages;
											  this.afterTextItem.setText(String.format(this.afterPageText,d.pages));
											  this.inputItem.setValue(ap);
											  this.first.setDisabled(ap==1);
											  this.prev.setDisabled(ap==1);
											  this.next.setDisabled(ap==ps);
											  this.last.setDisabled(ap==ps);
											  this.refresh.enable();
											  this.updateInfo();
											  if(this.rendered&&parseInt(this.getActivePageField().getValue())!==ap)
											  {
												  this.fireEvent("change",this,d);
											  }
										  },
								getActivePageField:function()
													{
														if(!this.activePageField)
														{
															this.activePageField=new Ext.form.Hidden(	{
																											id:this.id+"_ActivePage",
																											name:this.id+"_ActivePage"
																										}
																									);
															this.on("beforedestroy",function()
																					{
																						if(this.rendered)
																						{
																							this.destroy();
																						}
																					},
																					this.activePageField
																	);
														}
														return this.activePageField;
													},
								fixFirstLayout:function()
												{
													if(this.dsLoaded)
													{
														this.onLoad.apply(this,this.dsLoaded);
													}
												}
								}
							);

Ext.PagingToolbar.prototype.onRender=Ext.PagingToolbar.prototype.onRender.createSequence(	function(el)
																							{
																								if(this.pageIndex)
																								{
																									if(this.store.getCount()===0)
																									{
																										this.store.on("load",	function()
																																{
																																	this.changePage(this.pageIndex);
																																},
																																this,
																																{
																																	single:true
																																}
																													);
																									}
																									else
																									{
																										this.changePage(this.pageIndex);
																									}
																								}
																								this.on("change",	function(el,data)
																													{
																														this.getActivePageField().setValue(data.activePage);
																													},
																													this
																										);
																								this.getActivePageField().render(this.el.parent()||this.el);
																								if(this.store.proxy.isMemoryProxy)
																								{
																									this.refresh.setHandler(	function()
																																{
																																	if(this.store.proxy.refreshData)
																																	{
																																		this.store.proxy.refreshData(null,this.store);
																																	}
																																	if(this.store.proxy.isUrl)
																																	{
																																		item.initialConfig.handler();
																																	}
																																},
																																this
																															);
																								}
																								if(this.hideRefresh)
																								{
																									this.refresh.hide();
																								}
																							}
																						);
																						
Ext.PagingToolbar.prototype.initComponent=Ext.PagingToolbar.prototype.initComponent.createSequence(		function()
																										{
																											if(this.ownerCt instanceof Ext.grid.GridPanel)
																											{
																												this.ownerCt.on	("viewready",
																																	this.fixFirstLayout,
																																	this,
																																	{
																																		single:true
																																	}
																																);
																											}
																											else
																											{
																												this.on("afterlayout",
																															this.fixFirstLayout,
																															this,
																															{
																																single:true
																															}
																														);
																											}
																										}
																									);

Ext.ux.PagingToolbar=Ext.extend(Ext.PagingToolbar,
													{
															onLoad:function(store,r,o)
																	{
																		if(!this.rendered)
																		{
																				this.dsLoaded=[store,r,o];return;
																		}
																		var p=this.getParams();
																		this.cursor=(o.params&&o.params[p.start])?o.params[p.start]:0;this.onChange();
																	},
															onChange:function()
																	{
																		
																		var t=this.store.getTotalCount(),s=this.pageSize;
																		if(t===0)
																		{
																			this.cursor=0;
																		}
																		else 
																		{
																			
																			if(this.cursor>=t)
																			{
																				this.cursor=(Math.ceil(t/s)-1)*s;
																			}
																		}

																		var d=this.getPageData(),
																		ap=d.activePage,
																		ps=d.pages;
																		ap=ap>ps?ps:ap;
																		this.afterTextItem.setText(String.format(this.afterPageText,d.pages));
																		this.inputItem.setValue(ap);
																		this.first.setDisabled(ap===1);
																		this.prev.setDisabled(ap===1);
																		this.next.setDisabled(ap===ps);
																		this.last.setDisabled(ap===ps);
																		this.refresh.enable();
																		this.updateInfo();
																		if(this.rendered&&parseInt(this.getActivePageField().getValue())!==ap)
																		{
																			this.fireEvent("change",this,d);
																		}
																	},
															onClear:function()
																	{
																		this.cursor=0;
																		this.onChange();
																	},
															doRefresh:function()
																	{
																		delete this.store.lastParams;
																		this.doLoad(this.cursor);
																	},
															bindStore:function(store,initial)
																		{
																			var doLoad;
																			if(!initial&&this.store)
																			{
																				if(store!==this.store&&this.store.autoDestroy)
																				{
																					this.store.destroy();
																				}
																				else
																				{
																					this.store.un("beforeload",this.beforeLoad,this);
																					this.store.un("load",this.onLoad,this);
																					this.store.un("exception",this.onLoadError,this);
																					this.store.un("datachanged",this.onChange,this);
																					this.store.un("add",this.onChange,this);
																					this.store.un("remove",this.onChange,this);
																					this.store.un("clear",this.onClear,this);
																				}
																				if(!store)
																				{
																					this.store=null;
																				}
																			}
																			if(store)
																			{
																				store=Ext.StoreMgr.lookup(store);
																				store.on	(
																								{
																									scope:this,
																									beforeload:this.beforeLoad,
																									load:this.onLoad,
																									exception:this.onLoadError,
																									datachanged:this.onChange,
																									add:this.onChange,
																									remove:this.onChange,
																									clear:this.onClear
																								}
																							);
																				doLoad=true;}
																				this.store=store;
																				if(doLoad)
																				{
																					this.onLoad(store,null,{});
																				}
																			}
																		}
								);
Ext.reg("ux.paging",Ext.ux.PagingToolbar);