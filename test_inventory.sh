#!/bin/bash

# Configuration
GATEWAY_URL="http://localhost:8000"

echo "=== PRUEBAS DEL SERVICIO DE INVENTARIO A TRAVÉS DEL GATEWAY ==="
echo ""

# Limpiar inventario existente
echo "0. Limpiando inventario existente..."
curl -s -X DELETE $GATEWAY_URL/inventory/1 2>/dev/null || echo "   (No hay endpoint DELETE o ya está limpio)"
echo ""

# 1. Crear inventario
echo "1. Crear inventario para libro ID 1..."
curl -s -X POST $GATEWAY_URL/inventory \
  -H "Content-Type: application/json" \
  -d '{"book_id":1,"quantity":100}' | jq .
echo ""

# 2. Ver inventario
echo "2. Ver inventario del libro ID 1..."
curl -s $GATEWAY_URL/inventory/book/1 | jq .
echo ""

# 3. Reservar 10 unidades
echo "3. Reservar 10 unidades..."
curl -s -X POST $GATEWAY_URL/inventory/reserve \
  -H "Content-Type: application/json" \
  -d '{"book_id":1,"quantity":10}' | jq .
echo ""

# 4. Ver después de reserva
echo "4. Ver disponibilidad actual..."
curl -s $GATEWAY_URL/inventory/book/1 | jq .
echo ""

# 5. Intentar reservar más de lo disponible
echo "5. Intentar reservar más de lo disponible (debería fallar)..."
curl -s -X POST $GATEWAY_URL/inventory/reserve \
  -H "Content-Type: application/json" \
  -d '{"book_id":1,"quantity":200}' | jq .
echo ""

# 6. Liberar unidades
echo "6. Liberar 5 unidades..."
curl -s -X POST $GATEWAY_URL/inventory/release \
  -H "Content-Type: application/json" \
  -d '{"book_id":1,"quantity":5}' | jq .
echo ""

# 7. Ver estado final
echo "7. Ver inventario actualizado..."
curl -s $GATEWAY_URL/inventory/book/1 | jq .
echo ""

echo "=== FIN DE PRUEBAS ==="