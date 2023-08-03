
Ext.onReady(inicializar);

function inicializar()
{
	if(parent.parent.gE('temporal').value)
	{	gE('txtUrl').value=parent.parent.gE('temporal').value;
		gE('txtUrl').readOnly = true;
		gE('Proto').style.display = "none"; 
	}
}


    
    
