(function () {
    'use strict';

    var CART_KEY = 'wience_cart_ativo';
    var products = [];
    var cart = loadCart();

    var $grid = document.getElementById('grid');
    var $status = document.getElementById('status');
    var $search = document.getElementById('search');
    var $sort = document.getElementById('sort');
    var $onlyStock = document.getElementById('only-stock');
    var $cartBtn = document.getElementById('cart-btn');
    var $cartCount = document.getElementById('cart-count');
    var $cartPanel = document.getElementById('cart-panel');
    var $cartList = document.getElementById('cart-list');
    var $cartTotal = document.getElementById('cart-total');
    var $cartClear = document.getElementById('cart-clear');

    function brl(value) {
        return value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    function loadCart() {
        try {
            return JSON.parse(localStorage.getItem(CART_KEY)) || {};
        } catch (err) {
            return {};
        }
    }

    function saveCart() {
        try {
            localStorage.setItem(CART_KEY, JSON.stringify(cart));
        } catch (err) { /* armazenamento indisponível: carrinho vale só nesta sessão */ }
    }

    function el(tag, className, text) {
        var node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    }

    function visibleProducts() {
        var term = $search.value.trim().toLowerCase();
        var list = products.filter(function (p) {
            if ($onlyStock.checked && p.stock <= 0) return false;
            return !term || p.name.toLowerCase().indexOf(term) !== -1;
        });

        switch ($sort.value) {
            case 'price-asc':  list.sort(function (a, b) { return a.price - b.price; }); break;
            case 'price-desc': list.sort(function (a, b) { return b.price - a.price; }); break;
            case 'name':       list.sort(function (a, b) { return a.name.localeCompare(b.name, 'pt-BR'); }); break;
            default:           list.sort(function (a, b) { return b.id - a.id; });
        }
        return list;
    }

    function renderProducts() {
        var list = visibleProducts();
        var frag = document.createDocumentFragment();

        list.forEach(function (p) {
            var card = el('article', 'card');
            card.appendChild(el('h2', null, p.name));
            card.appendChild(el('p', null, p.description));

            var badge = p.stock > 0
                ? el('span', 'badge', 'Em estoque: ' + p.stock)
                : el('span', 'badge off', 'Esgotado');
            card.appendChild(badge);

            var row = el('div', 'row');
            var priceBox = el('div');
            if (p.old_price && p.old_price > p.price) {
                priceBox.appendChild(el('small', 'old', brl(p.old_price)));
            }
            priceBox.appendChild(el('span', 'price', brl(p.price)));
            row.appendChild(priceBox);

            var btn = el('button', null, 'Adicionar');
            btn.type = 'button';
            btn.disabled = p.stock <= 0;
            btn.addEventListener('click', function () { addToCart(p); });
            row.appendChild(btn);

            card.appendChild(row);
            frag.appendChild(card);
        });

        $grid.textContent = '';
        $grid.appendChild(frag);
        $status.textContent = list.length
            ? list.length + ' produto(s) exibido(s).'
            : 'Nenhum produto encontrado.';
    }

    function addToCart(p) {
        var item = cart[p.id] || { name: p.name, price: p.price, qty: 0 };
        if (item.qty < p.stock) item.qty += 1;
        cart[p.id] = item;
        saveCart();
        renderCart();
    }

    function renderCart() {
        var ids = Object.keys(cart);
        var total = 0;
        var count = 0;

        $cartList.textContent = '';
        ids.forEach(function (id) {
            var item = cart[id];
            total += item.price * item.qty;
            count += item.qty;
            var li = el('li');
            li.appendChild(el('span', null, item.qty + ' x ' + item.name));
            li.appendChild(el('span', null, brl(item.price * item.qty)));
            $cartList.appendChild(li);
        });

        $cartCount.textContent = String(count);
        $cartTotal.textContent = brl(total);
    }

    function loadProducts() {
        fetch('produtos.json')
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function (data) {
                products = data;
                renderProducts();
            })
            .catch(function (err) {
                $status.textContent = 'Erro ao carregar produtos (' + err.message + ').';
            });
    }

    $search.addEventListener('input', renderProducts);
    $sort.addEventListener('change', renderProducts);
    $onlyStock.addEventListener('change', renderProducts);
    $cartBtn.addEventListener('click', function () { $cartPanel.hidden = !$cartPanel.hidden; });
    $cartClear.addEventListener('click', function () { cart = {}; saveCart(); renderCart(); });

    renderCart();
    loadProducts();
})();
