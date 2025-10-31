#!/bin/bash

echo "======================================"
echo "DIAGNÓSTICO COMPLETO DEL SISTEMA"
echo "======================================"
echo ""

# 1. Verificar estructura de directorios
echo "1. Verificando directorios físicos..."
echo "-----------------------------------"

dirs=(
    "Assets/documentos_oficiales"
    "Assets/documentos_oficiales/en_proceso"
    "Assets/documentos_oficiales/completados"
    "Assets/documentos_oficiales/archivado"
    "Assets/documentos_para_conocimiento"
    "Assets/archivos"
    "Assets/js/modulos"
)

for dir in "${dirs[@]}"; do
    if [ -d "$dir" ]; then
        echo "✅ $dir existe"
    else
        echo "❌ $dir NO EXISTE - NECESITA CREARSE"
    fi
done

echo ""

# 2. Verificar permisos
echo "2. Verificando permisos..."
echo "-----------------------------------"
ls -ld Assets/documentos_oficiales 2>/dev/null || echo "❌ Assets/documentos_oficiales NO EXISTE"
ls -ld Assets/archivos 2>/dev/null || echo "❌ Assets/archivos NO EXISTE"

echo ""

# 3. Verificar archivos PHP principales
echo "3. Verificando archivos PHP (sintaxis)..."
echo "-----------------------------------"

php_files=(
    "Controllers/HojaRuta.php"
    "Controllers/Calendario.php"
    "Controllers/Reportes.php"
    "Models/HojaRutaModel.php"
    "Models/CalendarioModel.php"
    "Models/ReportesModel.php"
    "Views/hoja_ruta/index.php"
    "Views/calendario/index.php"
    "Views/reportes/index.php"
    "Views/reportes/productividad.php"
)

for file in "${php_files[@]}"; do
    if [ -f "$file" ]; then
        result=$(php -l "$file" 2>&1 | grep "No syntax errors")
        if [ ! -z "$result" ]; then
            echo "✅ $file"
        else
            echo "❌ $file - ERROR DE SINTAXIS"
            php -l "$file"
        fi
    else
        echo "❌ $file - NO EXISTE"
    fi
done

echo ""

# 4. Verificar archivos JavaScript
echo "4. Verificando archivos JavaScript..."
echo "-----------------------------------"

js_files=(
    "Assets/js/modulos/reportes.js"
    "Assets/js/reportes_general.js"
)

for file in "${js_files[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file existe"
    else
        echo "❌ $file NO EXISTE"
    fi
done

echo ""

# 5. Verificar configuración
echo "5. Verificando configuración..."
echo "-----------------------------------"

if [ -f "Config/Config.php" ]; then
    echo "✅ Config/Config.php existe"
    grep "BASE_URL" Config/Config.php | head -1
    grep "DB_HOST" Config/Config.php | head -1
    grep "DB_NAME" Config/Config.php | head -1
else
    echo "❌ Config/Config.php NO EXISTE"
fi

echo ""

# 6. Verificar .htaccess
echo "6. Verificando .htaccess..."
echo "-----------------------------------"

if [ -f ".htaccess" ]; then
    echo "✅ .htaccess existe"
    cat .htaccess | head -10
else
    echo "❌ .htaccess NO EXISTE"
fi

echo ""

# 7. Verificar index.php
echo "7. Verificando index.php..."
echo "-----------------------------------"

if [ -f "index.php" ]; then
    echo "✅ index.php existe"
    php -l index.php
else
    echo "❌ index.php NO EXISTE"
fi

echo ""
echo "======================================"
echo "DIAGNÓSTICO COMPLETADO"
echo "======================================"
