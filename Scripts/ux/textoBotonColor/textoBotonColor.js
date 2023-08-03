Ext.form.TextoBotonColor = function(config)
{
	
    Ext.form.TextoBotonColor.superclass.constructor.call(this, config);
};

Ext.extend(Ext.form.TextoBotonColor, Ext.form.TriggerField,  
{
	
    triggerClass : 'x-form-TextoBotonColor-trigger',
    
    defaultAutoCreate : {tag: "input", type: "text", size: "10", maxlength: "6", autocomplete: "off"},

    validateValue : function(value)
	{
        if(!Ext.form.TextoBotonColor.superclass.validateValue.call(this, value))
		{
            return false;
        }
        if(value.length < 1){ // if it's blank and textfield didn't flag it then it's valid
             return true;
        }
        return true;
    },

    validateBlur : function()
	{
        return true;
    },

	
    
    getValue : function()
	{
        return Ext.form.TextoBotonColor.superclass.getValue.call(this) || "";
    },

    setValue : function(valor)
	{
		this.inicializarJColor();
		this.jColorPicker.fromString(valor);
        Ext.form.TextoBotonColor.superclass.setValue.call(this, valor);
    },
	
	
	onFocus:function(e)
			{
				this.inicializarJColor();
			},
	
	

   	onBlur:function(e)
	{
		this.inicializarJColor();
		this.jColorPicker.hidePicker();
		this.setValue(this.getValue());
		
	},
	
    onTriggerClick : function(e)
	{

        if(this.disabled){
            return;
        }
		this.inicializarJColor();
		this.jColorPicker.showPicker();
		
			
    },
	
	inicializarJColor:function()
					{
						if(!this.jColorPicker)
							this.jColorPicker=new jscolor.color(document.getElementById(this.id), {pickerOnfocus:false});
					}
	
});
Ext.reg('textoBotonColor', Ext.form.TextoBotonColor);

