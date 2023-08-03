function calcularImporteNeto()
{
	var gConcepto=gEx('gConcepto');
    var fila;
    var x;
    var importeNeto=0;
    for(x=0;x<gConcepto.getStore().getCount();x++)
    {
    	fila=gConcepto.getStore().getAt(x);
        importeNeto+=(parseFloat(fila.data.costoUnitario)*parseFloat(fila.data.cantidad));
    }
    return Ext.util.Format.number(importeNeto,'0.00');
}

function calcularDescuentos()
{
	var gConcepto=gEx('gConcepto');
    var fila;
    var x;
    var resultado=0;
    for(x=0;x<gConcepto.getStore().getCount();x++)
    {
    	fila=gConcepto.getStore().getAt(x);
        resultado+=(parseFloat(fila.data.descuentoUnitario)*parseFloat(fila.data.cantidad));
    }
    if(resultado>0)
    	gEx('txtMotivoDescuento').enable();
    else
    {
    	gEx('txtMotivoDescuento').setValue('');
    	gEx('txtMotivoDescuento').disable();
    }
    return Ext.util.Format.number(resultado,'0.00');
}

function calcularSubtotal()
{
	var gConcepto=gEx('gConcepto');
    var fila;
    var x;
    var resultado=0;
    for(x=0;x<gConcepto.getStore().getCount();x++)
    {
    	fila=gConcepto.getStore().getAt(x);
        resultado+=(parseFloat(fila.data.subtotal));
    }
    return Ext.util.Format.number(resultado,'0.00');
}

function calcularIVA()
{
	var gConcepto=gEx('gConcepto');
    var fila;
    var x;
    var resultado=0;
    for(x=0;x<gConcepto.getStore().getCount();x++)
    {
    	fila=gConcepto.getStore().getAt(x);
        resultado+=(parseFloat(fila.data.iva));
    }

    return Ext.util.Format.number(resultado,'0.00');
}

function calcularSubtotalIVA()
{
	var gMontosFinales=gEx('gMontosFinales');
	var pos=obtenerPosFila(gMontosFinales.getStore(),'idConcepto','7');
    var subtotal=0;
    if(pos!=-1)
	    subtotal=parseFloat(gMontosFinales.getStore().getAt(pos).data.montoConcepto);
    pos=obtenerPosFila(gMontosFinales.getStore(),'idConcepto','1');
    var iva=0;
    if(pos!=-1)
	    iva=parseFloat(gMontosFinales.getStore().getAt(pos).data.montoConcepto);
        
        
    return Ext.util.Format.number((subtotal+iva),'0.00');    

}

function calcularRetencionIVA2_3()
{
	var gMontosFinales=gEx('gMontosFinales');
	
    var pos=obtenerPosFila(gMontosFinales.getStore(),'idConcepto','1');
    var iva=0;
    if(pos!=-1)
	    iva=parseFloat(gMontosFinales.getStore().getAt(pos).data.montoConcepto);
        
    var proporcionIVA=iva/3;
    var retencion=parseFloat(Ext.util.Format.number(proporcionIVA*2,'0.00'));
   	return Ext.util.Format.number(retencion,'0.00');

}

function calcularRetencionISR()
{
	var gMontosFinales=gEx('gMontosFinales');
	var pos=obtenerPosFila(gMontosFinales.getStore(),'idConcepto','7');
    var subtotal=0;
    if(pos!=-1)
	    subtotal=parseFloat(gMontosFinales.getStore().getAt(pos).data.montoConcepto);
    pos=obtenerPosFila(gMontosFinales.getStore(),'idConcepto','3');
    var porcentajeRetencion=0;
    if(pos!=-1)
	    porcentajeRetencion=gMontosFinales.getStore().getAt(pos).data.tasaConcepto;
    if(porcentajeRetencion=='')
    	porcentajeRetencion=0;
    else
    	porcentajeRetencion=parseFloat(porcentajeRetencion);
        
    var montoRetencion=subtotal*(porcentajeRetencion/100);  
    return Ext.util.Format.number(montoRetencion,'0.00');  

}

function calcularSubtotalRetISR()
{
	var gMontosFinales=gEx('gMontosFinales');
	
    var subtotal=0;
    var pos=obtenerPosFila(gMontosFinales.getStore(),'idConcepto','3');
    var montoRetencion=0;
    if(pos!=-1)
	    montoRetencion=gMontosFinales.getStore().getAt(pos).data.montoConcepto;
    if(montoRetencion=='')
    	montoRetencion=0;
    else
    	montoRetencion=parseFloat(montoRetencion);
       
       
    subtotal=parseFloat(obtenerSubtotalActual(pos));   
        
    var montoRetencion=subtotal-montoRetencion;    
    return Ext.util.Format.number(montoRetencion,'0.00');  

}

function calcularRetencionIVA()
{
	var gConcepto=gEx('gConcepto');
    var gMontosFinales=gEx('gMontosFinales');
    var pos=obtenerPosFila(gMontosFinales.getStore(),'idConcepto','14');
    var porcentajeRetencion=0;
    if(pos!=-1)
	    porcentajeRetencion=gMontosFinales.getStore().getAt(pos).data.tasaConcepto;
    if(porcentajeRetencion=='')
    	porcentajeRetencion=0;
    else
    	porcentajeRetencion=parseFloat(porcentajeRetencion);
        
    porcentajeRetencion=porcentajeRetencion/100;    
   	var gConcepto=gEx('gConcepto');
    var fila;
    var x;
    var importeNeto=0;
    var totalRetencion=0;
    for(x=0;x<gConcepto.getStore().getCount();x++)
    {
        fila=gConcepto.getStore().getAt(x);
        if((fila.data.tasaIVA!='')&&(fila.data.tasaIVA!='0'))
        {
        	totalRetencion=(parseFloat(fila.data.subtotal)*porcentajeRetencion);
        }

    }
    return Ext.util.Format.number(totalRetencion,'0.00');  
}


function obtenerSubtotalActual(posActual)
{
	var total=0;
    var gMontosFinales=gEx('gMontosFinales');
    var x;
    var fila;
    for(x=0;x<posActual;x++)
    {
    	fila=gMontosFinales.getStore().getAt(x);
        
        switch(fila.data.tipoConcepto)
        {
        	case '1': //impuesto
            	total+=parseFloat(fila.data.montoConcepto);
            
            break;
            case '3':
            case '2': //retencion
            	total-=parseFloat(fila.data.montoConcepto);
            	
            break;
        }
        switch(fila.data.idConcepto)
        {
        	case '7':
            case '1':
            	total+=parseFloat(fila.data.montoConcepto);
            break;
        }
    }
    return Ext.util.Format.number(total,'0.00');  
}

function calcularTotal()
{
	var total=0;
    var gMontosFinales=gEx('gMontosFinales');
    var x;
    var fila;
    for(x=0;x<gMontosFinales.getStore().getCount();x++)
    {
    	fila=gMontosFinales.getStore().getAt(x);
        
        switch(fila.data.tipoConcepto)
        {
        	case '1': //impuesto
            	total+=parseFloat(fila.data.montoConcepto);
            
            break;
            case '3':
            case '2': //retencion
            	total-=parseFloat(fila.data.montoConcepto);
            	
            break;
        }
        switch(fila.data.idConcepto)
        {
        	case '7':
            case '1':
            	total+=parseFloat(fila.data.montoConcepto);
            break;
        }
    }
    return Ext.util.Format.number(total,'0.00');  

    
}


function calcularTotales()
{

	var arr;
    var gMontosFinales=gEx('gMontosFinales');
    var fila;
    var x;
    for(x=0;x<gMontosFinales.getStore().getCount();x++)
    {
    	fila=gMontosFinales.getStore().getAt(x);
        switch(fila.data.tipoConcepto)
        {
            case '1':
                arr=arrImpuestos;
                
                
            break;
            case '2':
                arr=arrRetenciones;
                
            break;
            case '0':
                arr=arrIntermedios;
                
            break;
            case '3':
                arr=arrDescuentos;
            break;
        }
    	var val=fila.data.idConcepto;
        var pos=existeValorMatriz(arr,val);
        var filaConcepto=arr[pos];
        
        if((filaConcepto[2]!='')&&(!gEx('chkDesCalculoTotal').getValue()))
        {
            
            var res=0;
            eval('res='+filaConcepto[2]+';');
    		fila.set('montoConcepto',res);
           
           
        }
	}
}


function getValorCero()
{
	return 0
}