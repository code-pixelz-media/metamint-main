/* Metamint Apps — interactions: FAQ accordion, chat demo, SEO score demo, sandbox checkout modal. */
(function () {
  "use strict";

  /* FAQ accordion */
  document.querySelectorAll("[data-mm-faq]").forEach(function (root) {
    root.querySelectorAll(".mm-faq-q").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var item = btn.closest(".mm-faq-item");
        var answer = item.querySelector(".mm-faq-a");
        var isOpen = item.classList.contains("is-open");
        root.querySelectorAll(".mm-faq-item").forEach(function (i) {
          i.classList.remove("is-open");
          i.querySelector(".mm-faq-a").hidden = true;
        });
        if (!isOpen) {
          item.classList.add("is-open");
          answer.hidden = false;
        }
      });
    });
  });

  /* AI chat demo */
  var REPLIES = [
    [["refund", "money", "return"], "Happy to help with refunds! You can request one within 30 days from your account page — I can start that for you right now."],
    [["price", "cost", "plan", "license"], "We have three license tiers: Single Site $49, 5 Sites $99 and Unlimited $149 per year. Want a link to checkout?"],
    [["install", "setup", "wordpress"], "Installation takes about a minute: Plugins → Add New → search 'Metamint Helpdesk' → Activate. I'll walk you through settings after that."],
    [["ai", "agent", "bot"], "That's me! I learn from your docs and past tickets. If I ever get stuck, I hand the conversation to a human teammate seamlessly."]
  ];
  function botReply(q) {
    var lower = q.toLowerCase();
    for (var i = 0; i < REPLIES.length; i++) {
      if (REPLIES[i][0].some(function (k) { return lower.indexOf(k) !== -1; })) return REPLIES[i][1];
    }
    return "Great question! I'd normally pull the answer from your knowledge base. Try asking about refunds, pricing, installation, or the AI agent.";
  }
  document.querySelectorAll("[data-mm-chat]").forEach(function (root) {
    var log = root.querySelector("[data-mm-chat-log]");
    var input = root.querySelector("[data-mm-chat-input]");
    var send = root.querySelector("[data-mm-chat-send]");
    function add(text, who) {
      var b = document.createElement("div");
      b.className = "mm-bubble mm-bubble-" + who;
      b.textContent = text;
      log.appendChild(b);
      log.scrollTop = log.scrollHeight;
    }
    function submit() {
      var q = input.value.trim();
      if (!q) return;
      add(q, "user");
      input.value = "";
      setTimeout(function () { add(botReply(q), "bot"); }, 650);
    }
    send.addEventListener("click", submit);
    input.addEventListener("keydown", function (e) { if (e.key === "Enter") submit(); });
  });

  /* SEO score demo */
  document.querySelectorAll("[data-mm-score]").forEach(function (root) {
    var url = root.querySelector("[data-mm-score-url]");
    var kw = root.querySelector("[data-mm-score-kw]");
    var run = root.querySelector("[data-mm-score-run]");
    var result = root.querySelector("[data-mm-score-result]");
    var num = root.querySelector("[data-mm-score-num]");
    var fill = root.querySelector("[data-mm-score-fill]");
    var checks = root.querySelector("[data-mm-score-checks]");
    var CHECKS = [
      ["Title tag optimized", 45], ["Meta description present", 55], ["Product schema (JSON-LD)", 62],
      ["Image alt texts", 50], ["Core Web Vitals pass", 72], ["Internal linking depth", 66]
    ];
    run.addEventListener("click", function () {
      if (!url.value.trim()) return;
      var s = (url.value + "|" + (kw.value || "shopify")).toLowerCase();
      var h = 0;
      for (var i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) % 997;
      var score = 42 + (h % 57);
      var color = score >= 75 ? "#0F7173" : score >= 55 ? "#D8A47F" : "#F05D5E";
      result.hidden = false;
      num.textContent = score;
      num.style.background = color;
      fill.style.background = color;
      requestAnimationFrame(function () { fill.style.width = score + "%"; });
      checks.innerHTML = "";
      CHECKS.forEach(function (c) {
        var li = document.createElement("li");
        li.className = score >= c[1] ? "pass" : "fail";
        li.textContent = (score >= c[1] ? "✓ " : "! ") + c[0];
        checks.appendChild(li);
      });
    });
  });

  /* Sandbox demo checkout modal */
  var PLANS = {
    helpdesk: { name: "Metamint Helpdesk", plans: [["Single Site", 49], ["5 Sites", 99], ["Unlimited", 149]] },
    seo: { name: "Metamint SEO", plans: [["Basic", 29], ["Pro", 59], ["Agency", 119]] }
  };

  var modal = document.createElement("div");
  modal.className = "mm-modal";
  modal.setAttribute("data-testid", "mock-checkout-modal");
  modal.innerHTML =
    '<div class="mm-modal-veil" data-mm-close></div>' +
    '<div class="mm-modal-card" role="dialog" aria-label="Demo checkout">' +
    '  <div class="mm-modal-head"><h3>Demo Checkout</h3><span class="mm-pill mm-pill-tan">Sandbox — No charge</span><button class="mm-modal-close" data-mm-close aria-label="Close">×</button></div>' +
    '  <div class="mm-modal-steps"><i></i><i></i><i></i><i></i></div>' +
    '  <div class="mm-modal-body" data-mm-body></div>' +
    '</div>';
  document.addEventListener("DOMContentLoaded", function () { document.body.appendChild(modal); });

  var state = { product: "helpdesk", plan: 1, step: 0 };

  function esc(s) { var d = document.createElement("div"); d.textContent = s; return d.innerHTML; }

  function render() {
    var bars = modal.querySelectorAll(".mm-modal-steps i");
    bars.forEach(function (b, i) { b.classList.toggle("on", i <= state.step); });
    var body = modal.querySelector("[data-mm-body]");
    var product = PLANS[state.product];

    if (state.step === 0) {
      body.innerHTML =
        '<p class="mm-eyebrow">01 — Pick your plan</p>' +
        '<div class="mm-plan-pick">' +
        product.plans.map(function (p, i) {
          return '<button type="button" data-mm-plan="' + i + '" class="' + (i === state.plan ? "on" : "") + '"><span><b>' + esc(p[0]) + '</b> / year</span><b>$' + p[1] + '</b></button>';
        }).join("") +
        "</div>" +
        '<button class="mm-btn mm-btn-ink" data-mm-next>Continue</button>';
      body.querySelectorAll("[data-mm-plan]").forEach(function (b) {
        b.addEventListener("click", function () { state.plan = parseInt(b.getAttribute("data-mm-plan"), 10); render(); });
      });
    } else if (state.step === 1) {
      body.innerHTML =
        '<p class="mm-eyebrow">02 — License holder</p>' +
        '<input type="text" placeholder="Your name" data-mm-name>' +
        '<input type="email" placeholder="you@shop.com" data-mm-email>' +
        '<button class="mm-btn mm-btn-ink" data-mm-next>Continue</button>';
    } else if (state.step === 2) {
      body.innerHTML =
        '<p class="mm-eyebrow">03 — Test card (pre-filled)</p>' +
        '<div class="mm-modal-note">Test mode: card 4242 4242 4242 4242 — nothing is charged.</div>' +
        '<input type="text" value="4242 4242 4242 4242" readonly>' +
        '<button class="mm-btn mm-btn-ink" data-mm-next>Pay $' + product.plans[state.plan][1] + " (fake)</button>";
    } else {
      var seg = function () { return Math.random().toString(36).slice(2, 6).toUpperCase(); };
      body.innerHTML =
        '<p class="mm-eyebrow">04 — Done</p>' +
        '<h4 class="mm-h4" style="text-align:center">Order complete</h4>' +
        '<p class="mm-muted" style="text-align:center">Your demo license for ' + esc(product.name) + " (" + esc(product.plans[state.plan][0]) + ") is ready.</p>" +
        '<div class="mm-license">METAMINT-' + seg() + "-" + seg() + "-" + seg() + "</div>" +
        '<p class="mm-fineprint">Sandbox demo — no real purchase was made</p>' +
        '<button class="mm-btn mm-btn-ink" data-mm-close>Done</button>';
    }
    body.querySelectorAll("[data-mm-next]").forEach(function (b) {
      b.addEventListener("click", function () {
        if (state.step === 1) {
          var name = body.querySelector("[data-mm-name]").value.trim();
          var email = body.querySelector("[data-mm-email]").value;
          if (!name || email.indexOf("@") === -1) return;
        }
        state.step++;
        render();
      });
    });
    body.querySelectorAll("[data-mm-close]").forEach(function (b) {
      b.addEventListener("click", close);
    });
  }

  function open(product) {
    state = { product: PLANS[product] ? product : "helpdesk", plan: 1, step: 0 };
    render();
    modal.classList.add("is-open");
    document.body.style.overflow = "hidden";
  }
  function close() {
    modal.classList.remove("is-open");
    document.body.style.overflow = "";
  }

  document.addEventListener("click", function (e) {
    var trigger = e.target.closest("[data-mm-checkout]");
    if (trigger) {
      e.preventDefault();
      open(trigger.getAttribute("data-mm-product"));
    }
    if (e.target.closest("[data-mm-close]") && e.target.closest(".mm-modal")) close();
  });
  document.addEventListener("keydown", function (e) { if (e.key === "Escape") close(); });

  /* Buy: add product to cart (AJAX) then swap the button to "View cart" + "Checkout". */
  document.addEventListener("click", function (e) {
    var btn = e.target.closest("[data-mm-add-to-cart]");
    if (!btn) return;
    e.preventDefault();
    var pid = btn.getAttribute("data-mm-add-to-cart");
    var checkoutUrl = btn.getAttribute("data-mm-checkout-url");
    var cartUrl = btn.getAttribute("data-mm-cart-url");
    if (!pid || btn.classList.contains("is-busy")) return;
    btn.classList.add("is-busy");
    var label = btn.textContent;
    btn.textContent = "Adding\u2026";
    var endpoint = window.location.origin + window.location.pathname + "?wc-ajax=add_to_cart";
    var body = new URLSearchParams();
    body.set("product_id", pid);
    body.set("quantity", "1");
    fetch(endpoint, { method: "POST", credentials: "same-origin", body: body })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data && data.fragments) {
          btn.classList.remove("is-busy");
          var isBlock = btn.classList.contains("mm-btn-block");
          var view = document.createElement("a");
          view.className = btn.className;
          if (btn.getAttribute("style")) view.setAttribute("style", btn.getAttribute("style"));
          view.href = cartUrl;
          view.textContent = "View cart";
          view.setAttribute("data-testid", btn.getAttribute("data-testid"));
          var checkout = document.createElement("a");
          checkout.className = "mm-btn mm-btn-outline " + (isBlock ? "mm-btn-block " : "mm-btn-sm ") + "mm-view-cart";
          checkout.href = checkoutUrl;
          checkout.textContent = "Checkout";
          checkout.setAttribute("data-testid", "view-cart");
          btn.parentNode.replaceChild(view, btn);
          view.parentNode.insertBefore(checkout, view.nextSibling);
        } else {
          btn.classList.remove("is-busy");
          btn.textContent = label;
        }
      })
      .catch(function () {
        btn.classList.remove("is-busy");
        btn.textContent = label;
      });
  });
})();
