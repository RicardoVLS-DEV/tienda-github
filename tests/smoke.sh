#!/usr/bin/env bash
# Prueba de humo end-to-end: recorre registro, login y CRUD contra un servidor real.
# Uso: BASE_URL=http://127.0.0.1:8000 bash tests/smoke.sh
set -u
BASE="${BASE_URL:-http://127.0.0.1:8000}/index.php"
JAR="$(mktemp)"; trap 'rm -f "$JAR"' EXIT
EMAIL="smoke_$(date +%s)_$RANDOM@mail.com"
PASS_OK=0; PASS_FAIL=0

ok()   { echo "  ✔ $1"; PASS_OK=$((PASS_OK+1)); }
fail() { echo "  ✘ $1"; PASS_FAIL=$((PASS_FAIL+1)); }
has()  { grep -q -- "$2" <<<"$1" && ok "$3" || fail "$3 (no aparece: $2)"; }
hasnt(){ grep -q -- "$2" <<<"$1" && fail "$3 (aparece: $2)" || ok "$3"; }
get()  { curl -s -L -b "$JAR" -c "$JAR" "$BASE?r=$1"; }
code() { curl -s -o /dev/null -w '%{http_code}' -b "$JAR" -c "$JAR" "${@}"; }
token(){ grep -o 'name="_token" value="[^"]*"' <<<"$1" | head -1 | sed 's/.*value="//; s/"//'; }
post() { local r="$1"; shift; curl -s -L -b "$JAR" -c "$JAR" --data-urlencode "_token=$TOKEN" "$@" "$BASE?r=$r"; }

echo "1) Páginas públicas"
HOME_HTML=$(get home);     has "$HOME_HTML" "Productos" "La portada carga"; has "$HOME_HTML" "Entrar" "Muestra enlace Entrar"
has "$(get login)" 'name="password"' "El login carga"
has "$(get register)" 'name="confirm"' "El registro carga"
[ "$(code "$BASE?r=noexiste")" = "404" ] && ok "Ruta inexistente responde 404" || fail "404"

echo "2) Rutas protegidas sin sesión"
LOC=$(curl -s -o /dev/null -w '%{redirect_url}' -b "$JAR" -c "$JAR" "$BASE?r=product.create")
grep -q "r=login" <<<"$LOC" && ok "Crear producto redirige al login" || fail "Redirección a login ($LOC)"
[ "$(code -X POST "$BASE?r=register" -d 'name=x')" = "419" ] && ok "POST sin token CSRF es rechazado (419)" || fail "CSRF"

echo "3) Registro"
TOKEN=$(token "$(get register)")
R=$(post register --data-urlencode "name=Ana" --data-urlencode "email=$EMAIL" --data-urlencode "password=abc" --data-urlencode "confirm=xyz")
has "$R" "al menos 8 caracteres" "Rechaza contraseña corta"; has "$R" "no coinciden" "Rechaza confirmación distinta"
R=$(post register --data-urlencode "name=Ana María" --data-urlencode "email=$EMAIL" --data-urlencode "password=secreto123" --data-urlencode "confirm=secreto123")
has "$R" "Hola, Ana" "Registro válido inicia sesión"; has "$R" "Cuenta creada" "Muestra mensaje de éxito"

echo "4) CRUD de productos"
FORM=$(get product.create); TOKEN=$(token "$FORM")
R=$(post product.store --data-urlencode "name=" --data-urlencode "price=-1" --data-urlencode "stock=x")
has "$R" "obligatorio" "Rechaza nombre vacío"; has "$R" "Precio debe ser" "Rechaza precio inválido"
R=$(post product.store --data-urlencode "name=Taza <b>azul</b>" --data-urlencode "description=Cerámica" --data-urlencode "price=12,50" --data-urlencode "stock=5")
has "$R" "Producto creado" "Crea producto"; has "$R" '\$12.50' "Acepta coma decimal y muestra precio"
has "$R" "&lt;b&gt;azul" "Escapa HTML del nombre (anti-XSS)"
ID=$(grep -o 'product.edit&amp;id=[0-9]*\|product.edit&id=[0-9]*' <<<"$R" | head -1 | grep -o '[0-9]*$')
[ -n "$ID" ] && ok "Obtiene el id del producto ($ID)" || fail "No se encontró el id"
EDIT=$(get "product.edit&id=$ID"); has "$EDIT" "Taza" "El formulario de edición carga los datos"; TOKEN=$(token "$EDIT")
R=$(post product.update --data-urlencode "id=$ID" --data-urlencode "name=Taza roja" --data-urlencode "description=" --data-urlencode "price=20" --data-urlencode "stock=9")
has "$R" "Producto actualizado" "Actualiza producto"; has "$R" "Taza roja" "Refleja el cambio"
R=$(post product.delete --data-urlencode "id=$ID")
has "$R" "Producto eliminado" "Elimina producto"; hasnt "$R" "Taza roja" "Ya no aparece en el listado"

echo "5) Logout y login"
TOKEN=$(token "$(get home)"); R=$(post logout)
has "$R" "Sesión cerrada" "Cierra sesión"
TOKEN=$(token "$(get login)")
R=$(post login --data-urlencode "email=$EMAIL" --data-urlencode "password=incorrecta1")
has "$R" "Credenciales incorrectas" "Rechaza contraseña incorrecta"
R=$(post login --data-urlencode "email=$EMAIL" --data-urlencode "password=secreto123")
has "$R" "Hola, Ana" "Login correcto"

echo; echo "Resultado: $PASS_OK correctas, $PASS_FAIL fallidas"
[ "$PASS_FAIL" -eq 0 ]
