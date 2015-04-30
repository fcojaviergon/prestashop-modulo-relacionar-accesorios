# prestashop-module-relacionar-accesorios

Modulo para prestashop que permite subir un archivo CSV para relacionar productos y accesorios.
Actualmente no existe una opción para relacionar producto como accesorios a la hora de cargar masivamente, este módulo permite  insertar o borrar accesorios, por medio de un archivo CSV, con una columna SKU (reference) y otra columna con ACCESORIOS SKU (reference) separados por ; permite cargar una relación entre si.

Funcionando para prestashop 1.6

Ejemplo CSV:
```
SKU,ACCESORIOS
Y41C00040,FOAM120EG
Y41C40BL0,FOAM120EG
Y41C00070,FOAM120EG;AE3000000
Y41C00080,FOAM120EG;AE3000000
Y41F370BL,FOAM120EG
Y41CX4000,FOAM120EG;AE3000000
```
La instalación es como cualquier modulo de prestashop y son libres de adaptar el modulo para que lea otro campo, la relación final es por id de producto.

La Administración

![captura de pantalla 2015-04-30 a la s 17 47 32](https://cloud.githubusercontent.com/assets/10155322/7422254/c182d84c-ef61-11e4-812b-9a1af6def99e.png)
