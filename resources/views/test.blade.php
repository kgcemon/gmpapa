<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Diamond Packages</title>

    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{
            font-family:'Inter',sans-serif;
            background:linear-gradient(135deg,#0F0C29 0%,#302B63 50%,#24243e 100%);
            min-height:100vh;overflow-x:hidden;position:relative;color:white;
        }
        .glow-orb{position:absolute;border-radius:50%;filter:blur(40px);pointer-events:none;z-index:-1}
        .glow-orb-1{width:250px;height:250px;background:radial-gradient(circle,rgba(0,212,255,0.25)0%,transparent 70%);top:20%;left:10%;animation:float-glow 15s infinite ease-in-out}
        .glow-orb-2{width:180px;height:180px;background:radial-gradient(circle,rgba(255,0,110,0.25)0%,transparent 70%);top:60%;right:15%;animation:float-glow 20s infinite ease-in-out reverse}
        @keyframes float-glow{0%,100%{transform:translate(0,0)scale(1)}25%{transform:translate(25px,-15px)scale(1.05)}50%{transform:translate(-15px,25px)scale(.95)}75%{transform:translate(15px,15px)scale(1.02)}}
        .container{max-width:500px;margin:0 auto;padding:25px 18px;text-align:center;position:relative;z-index:1}
        .header{margin-bottom:30px}
        .product-title{font-family:'Orbitron',sans-serif;font-size:2rem;font-weight:700;background:linear-gradient(45deg,#00d4ff,#ff006e,#090979);background-size:200% 200%;-webkit-background-clip:text;-webkit-text-fill-color:transparent;animation:gradient-shift 4s ease infinite;text-shadow:0 0 20px rgba(0,212,255,.5);margin-bottom:6px}
        @keyframes gradient-shift{0%,100%{background-position:0% 50%}50%{background-position:100% 50%}}
        .product-subtitle{font-size:1rem;color:rgba(255,255,255,.85);margin-bottom:18px;font-weight:400}

        /* Section styling with step number */
        .selection-panel{
            background:linear-gradient(145deg,rgba(255,255,255,.08),rgba(255,255,255,.04));
            backdrop-filter:blur(20px);
            border:1px solid rgba(255,255,255,.2);
            border-radius:20px;
            padding:25px 20px 20px 20px;
            margin-bottom:25px;
            text-align:left;
            box-shadow:0 10px 25px rgba(0,0,0,.2);
            position:relative;
        }
        .selection-panel::before{
            content: attr(data-step);
            position:absolute;
            top:-15px;
            left:20px; /* Move to left */
            transform:none; /* Remove centering */
            background:#10b981;
            color:white;
            width:30px;
            height:30px;
            border-radius:50%;
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight:700;
            font-size:1rem;
            box-shadow:0 0 10px rgba(0,0,0,.3);
        }

        .selection-title{font-size:1rem;color:white;margin-bottom:15px;font-weight:600}

        /* Player ID box */
        .player-id-box label{color:white;display:block;margin-bottom:8px;font-weight:500;font-size:.9rem}
        .player-id-box input{width:100%;padding:12px 16px;border-radius:12px;border:2px solid rgba(255,255,255,.1);background:rgba(255,255,255,.1);color:white;font-size:.9rem;outline:none}
        .player-id-box input:focus{border-color:#00d4ff;box-shadow:0 0 12px rgba(0,212,255,.25)}
        .error-message{color:#ff6b6b;font-weight:500;font-size:.85rem;margin-top:8px;text-align:left}

        /* Diamond Packages grid */
        .diamond-options{
            display:grid;
            grid-template-columns:repeat(2,1fr);
            gap:12px;
        }
        .diamond-option{
            background:linear-gradient(145deg,rgba(255,255,255,.07),rgba(255,255,255,.03));
            border:2px solid rgba(255,255,255,.1);
            border-radius:14px;
            padding:12px 14px;
            cursor:pointer;
            transition:.3s;
            color:white;
            font-size:.7rem;
            font-weight:500;
            display:flex;
            justify-content:space-between;
            align-items:center;
        }
        .diamond-option .price{
            font-size:.5rem;
            font-weight:500;
            opacity:.85;
            padding-bottom: 5px;
        }
        .diamond-option.selected{
            background:linear-gradient(135deg,#00d4ff,#090979);
            border-color:#00d4ff;
            animation:pulse 2s infinite
        }
        @keyframes pulse{0%,100%{box-shadow:0 0 15px rgba(0,212,255,.4)}50%{box-shadow:0 0 15px rgba(0,212,255,.6)}}

        /* Payment Methods */
        .payment-methods{display:flex;gap:10px}
        .payment-option{flex:1;padding:12px 10px;border-radius:14px;border:2px solid rgba(255,255,255,.2);background:linear-gradient(145deg,rgba(255,255,255,.07),rgba(255,255,255,.03));color:white;cursor:pointer;text-align:center;transition:.3s;font-size:.9rem}
        .payment-option.selected{background:linear-gradient(135deg,#10b981,#059669);border-color:#10b981}
        .payment-details{margin-top:12px;color:white;font-size:.85rem;line-height:1.4}

        /* Checkout button */
        .checkout-btn{width:100%;padding:14px;border:none;border-radius:16px;background:linear-gradient(135deg,#10b981,#059669);color:white;font-weight:600;cursor:pointer;font-size:1rem;transition:.3s}
        .checkout-btn:hover{background:linear-gradient(135deg,#059669,#047857)}
    </style>
</head>
<body>

<div class="glow-orb glow-orb-1"></div>
<div class="glow-orb glow-orb-2"></div>

<div class="container">
    <div class="header">
        <h1 class="product-title">Premium Diamond Packages</h1>
        <p class="product-subtitle">Enter Player ID, Select Package & Payment</p>
    </div>

    <div class="selection-panel" data-step="1">
        <div class="player-id-box">
            <h2 class="selection-title">Player ID লিখুন</h2>
            <input type="text" id="playerId" placeholder="Enter your Player ID">
            <div class="error-message" id="playerError"></div>
        </div>
    </div>

    <div class="selection-panel" data-step="2">
        <h2 class="selection-title">ডায়মন্ড প্যাকেজ নির্বাচন করুন</h2>
        <div class="diamond-options" id="diamondOptions"></div>
        <div class="error-message" id="packageError"></div>
    </div>

    <div class="selection-panel" data-step="3">
        <h2 class="selection-title">পেমেন্ট পদ্ধতি নির্বাচন করুন</h2>
        <div class="payment-methods">
            <div class="payment-option" data-method="Bkash">Bkash</div>
            <div class="payment-option" data-method="Nagad">Nagad</div>
            <div class="payment-option" data-method="Rocket">Rocket</div>
        </div>
        <div class="payment-details" id="paymentDetails"></div>
    </div>

    <button class="checkout-btn" id="checkoutBtn">Submit Order</button>
</div>

<script>
    const diamondPackages=[
        {id:1,diamonds:115,price:100},
        {id:2,diamonds:240,price:200},
        {id:3,diamonds:610,price:500},
        {id:4,diamonds:1240,price:950},
        {id:5,diamonds:2530,price:1900},
        {id:6,diamonds:5060,price:3750}
    ];
    let selectedPackage=null,selectedPayment="Bkash";

    const paymentInstructions={
        "Bkash":"Send payment to Bkash Number: <strong>0123456789</strong>",
        "Nagad":"Send payment to Nagad Number: <strong>0987654321</strong>",
        "Rocket":"Send payment to Rocket Number: <strong>0112233445</strong>"
    };

    function init(){
        document.getElementById("diamondOptions").innerHTML=diamondPackages.map(p=>`
    <div class="diamond-option" data-id="${p.id}">
      <span>${p.diamonds} Diamond</span>
      <span class="price">${p.price}৳</span>
    </div>`).join('');

        document.querySelectorAll(".diamond-option").forEach(el=>{
            el.onclick=()=>{
                document.querySelectorAll(".diamond-option").forEach(o=>o.classList.remove("selected"));
                el.classList.add("selected");selectedPackage=+el.dataset.id;
                document.getElementById("packageError").textContent=""
            }
        });

        document.querySelectorAll(".payment-option").forEach(el=>{
            el.onclick=()=>{
                document.querySelectorAll(".payment-option").forEach(o=>o.classList.remove("selected"));
                el.classList.add("selected");selectedPayment=el.dataset.method;
                document.getElementById("paymentDetails").innerHTML=paymentInstructions[selectedPayment]+"<br>After payment, note TRX ID & Submit.";
            }
        });

        document.querySelector(`[data-method="${selectedPayment}"]`).classList.add("selected");
        document.getElementById("paymentDetails").innerHTML=paymentInstructions[selectedPayment];

        document.getElementById("checkoutBtn").onclick=()=>{
            const pid=document.getElementById("playerId").value.trim();
            let valid=true;
            if(!pid){document.getElementById("playerError").textContent="অনুগ্রহ করে Player ID লিখুন!";valid=false}
            else document.getElementById("playerError").textContent="";
            if(!selectedPackage){document.getElementById("packageError").textContent="অনুগ্রহ করে ডায়মন্ড প্যাকেজ নির্বাচন করুন!";valid=false}
            if(!valid)return;

            const pkg=diamondPackages.find(p=>p.id===selectedPackage);

            alert(`✅ Order Submitted!\n\nPlayer ID: ${pid}\nPackage: ${pkg.diamonds} Diamond\nPayment: ${selectedPayment}\nAmount: ${pkg.price}৳`);
        }
    }
    document.addEventListener("DOMContentLoaded",init);
</script>
</body>
</html>
