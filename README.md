# React + Symfony 3.4 + Sonata Bundle

Plantilla con configuración de symfony 3.4 + Reactjs

```
git clone https://github.com/gooapps/symfony3.4-template.git
cd reactsymfony
```

## Prerequisitos


* [PHP - Apache - Mysql](http://www.wampserver.com/en/)
* [Composer](https://getcomposer.org/)
* [Yarn](https://yarnpkg.com/lang/en/) 

## Symfony 


```
composer install
```

Escoger como base de datos una ya creada en el equipo, por ejemplo en este caso reactsymfony (párametro **databasename**), el resto dejar por defecto (darle a enter).

En caso de que composer no instale todos los assets la primera vez, volver a ejecturar **composer install**

## Yarn 

```
yarn install
```


## Ejecutar

Abrimos dos consolas, una para ejecutar symfony, y la otra para poder desarrollar componentes en react y que estos se vayan actualizando.

> Consola React
```
php bin/console server:run
```
> Consola Yarn

```
yarn run encore dev --watch
```

Nos vamos a http://localhost:8000 y accedemos a la página por defecto con nuestro componente de React ya hecho.

## Estructura React

Los ficheros fuente de React se encuentran en la carpeta **assets/js** , el componente base se inyecta en la aplicación en la plantilla **index.html.twig** el **div con id="root"**, y los js compilados se introduce su dirección en la plantilla **base.html.twig**

