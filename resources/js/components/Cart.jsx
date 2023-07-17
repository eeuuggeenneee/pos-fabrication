import React, { Component } from "react";
import ReactDOM from "react-dom";
import axios from "axios";
import Swal from "sweetalert2";
import { sum } from "lodash";
import jsPDF from "jspdf";
import html2canvas from "html2canvas";
import { saveAs } from "file-saver";




class Cart extends Component {
    constructor(props) {
        super(props);
        this.state = {
            cart: [],
            products: [],
            customers: [],
            barcode: "",
            search: "",
            customer_id: "",
            discount_id: "",
            promoCode: "",
            discountAmount: 0,
            grossTotal: 0,
            netTotal: 0,
            promoCodes: [],
            receiptData: null,
        };

        this.loadCart = this.loadCart.bind(this);
        this.handleOnChangeBarcode = this.handleOnChangeBarcode.bind(this);
        this.handleScanBarcode = this.handleScanBarcode.bind(this);
        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleEmptyCart = this.handleEmptyCart.bind(this);
        this.loadProducts = this.loadProducts.bind(this);
        this.handleChangeSearch = this.handleChangeSearch.bind(this);
        this.handleSeach = this.handleSeach.bind(this);
        this.setCustomerId = this.setCustomerId.bind(this);
        this.handleClickSubmit = this.handleClickSubmit.bind(this);
        this.handlePromoCodeChange = this.handlePromoCodeChange.bind(this);
    }

    componentDidMount() {
        this.loadCart();
        this.loadProducts();
        this.loadCustomers();
        this.loadPromoCodes();
    }

    loadCustomers() {
        axios.get(`/admin/customers`).then((res) => {
            const customers = res.data;
            this.setState({ customers });
        });
    }

    loadPromoCodes() {
        axios.get("/admin/discounts/promocode").then((res) => {
            const promoCodes = res.data;
            this.setState({ promoCodes });
        });
    }

    loadProducts(search = "") {
        const query = !!search ? `?search=${search}` : "";
        axios.get(`/admin/products${query}`).then((res) => {
            const products = res.data.data;
            this.setState({ products });
        });
    }

    handleOnChangeBarcode(event) {
        const barcode = event.target.value;
        this.setState({ barcode });
    }

    loadCart() {
        axios.get("/admin/cart").then((res) => {
            const cart = res.data;
            this.setState({ cart });
        });
    }

    handleScanBarcode(event) {
        event.preventDefault();
        const { barcode } = this.state;
        if (!!barcode) {
            axios
                .post("/admin/cart", { barcode })
                .then((res) => {
                    this.loadCart();
                    this.setState({ barcode: "" });
                })
                .catch((err) => {
                    Swal.fire("Error!", err.response.data.message, "error");
                });
        }
    }

    handleChangeQty(product_id, qty) {
        const cart = this.state.cart.map((c) => {
            if (c.id === product_id) {
                c.pivot.quantity = qty;
            }
            return c;
        });

        this.setState({ cart }, () => {
            const grossTotal = this.getTotal(this.state.cart);

            if (this.state.discountAmount == 0) {
                const equal = grossTotal * (window.APP.tax / 100);
                const netTotal = parseFloat(equal) + parseFloat(grossTotal);
                console.log(equal);
                this.setState({ grossTotal, netTotal });
            } else {
                const discount = parseFloat(this.state.discountAmount);
                const equal = parseFloat(grossTotal) - parseFloat(discount);
                const tax1 = equal * (window.APP.tax / 100);
                const netTotal = parseFloat(tax1) + parseFloat(equal);
                console.log(equal);
                this.setState({ grossTotal, netTotal });
            }
        });

        if (!qty) return;

        axios
            .post("/admin/cart/change-qty", {
                product_id,
                quantity: qty,
                dis_id: this.state.discount_id,
            })
            .then((res) => {})
            .catch((err) => {
                if (err.response.status === 400) {
                    const maxQty = parseInt(
                        err.response.data.message.split(": ")[1]
                    );
                    const cart = this.state.cart.map((c) => {
                        if (c.id === product_id) {
                            c.pivot.quantity = maxQty;
                        }
                        return c;
                    });
                    this.setState({ cart });
                    Swal.fire("Error!", err.response.data.message, "error");
                }
            });
    }

    getTotal(cart) {
        const total = cart.map((c) => c.pivot.quantity * c.price);
        return sum(total).toFixed(2);
    }

    handleClickDelete(product_id) {
        axios
            .post("/admin/cart/delete", { product_id, _method: "DELETE" })
            .then((res) => {
                const cart = this.state.cart.filter((c) => c.id !== product_id);
                this.setState({ cart });
            });
    }

    handleEmptyCart() {
        axios.post("/admin/cart/empty", { _method: "DELETE" }).then((res) => {
            this.setState({ cart: [] });
        });
    }

    handleChangeSearch(event) {
        const search = event.target.value;
        this.setState({ search });
    }

    handleSeach(event) {
        if (event.keyCode === 13) {
            this.loadProducts(event.target.value);
        }
    }

    addProductToCart(barcode) {
        let product = this.state.products.find((p) => p.barcode === barcode);
        if (!!product) {
            let cart = this.state.cart.find((c) => c.id === product.id);
            if (!!cart) {
                this.setState({
                    cart: this.state.cart.map((c) => {
                        if (
                            c.id === product.id &&
                            product.quantity > c.pivot.quantity
                        ) {
                            c.pivot.quantity = c.pivot.quantity + 1;
                        }
                        return c;
                    }),
                });
            } else {
                if (product.quantity > 0) {
                    product = {
                        ...product,
                        pivot: {
                            quantity: 1,
                            product_id: product.id,
                            user_id: 1,
                        },
                    };

                    this.setState({ cart: [...this.state.cart, product] });
                }
            }

            axios
                .post("/admin/cart", { barcode })
                .then((res) => {
                    console.log(res);
                })
                .catch((err) => {
                    Swal.fire("Error!", err.response.data.message, "error");
                });
        }
    }

    setCustomerId(event) {
        this.setState({ customer_id: event.target.value });
    }
    handlePromoCodeChange(event) {
        const promoCode = event.target.value;
        this.setState({ promoCode });

        axios.get(`/admin/discounts/promocode/${promoCode}`).then((res) => {
            const { isValid, discountAmount, discount_id } = res.data;
            console.log("Discount Amount:", discountAmount);
            console.log("Discount Amount:", discount_id);

            const grossTotal = this.getTotal(this.state.cart);
            const discount = parseFloat(discountAmount);
            const equal = grossTotal - discount;
            const tax =
                (parseFloat(grossTotal) - discount) * (window.APP.tax / 100);
            const netTotal = tax + equal;
            console.log(tax);

            if (isValid) {
                this.setState({
                    discountAmount,
                    grossTotal,
                    netTotal,
                    discount_id,
                });
            } else {
                Swal.fire("Error!", "Invalid discount code.", "error");
                this.setState({
                    discountAmount: 0,
                    grossTotal: this.getTotal(this.state.cart),
                    netTotal: this.getTotal(this.state.cart),
                    discountId: null,
                });
            }
        });
    }
    calculateTotal() {
        const { cart } = this.state;
        const total = cart.map((c) => c.pivot.quantity * c.price);
        return sum(total).toFixed(2);
    }

    generateRandomOrderId(length) {
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let result = '';
        for (let i = 0; i < length; i++) {
          result += characters.charAt(Math.floor(Math.random() * characters.length));
        }
        return result;
    }

    generatePDF(orderData) {
        const { receivedAmount,discountAmount,tax,reference_id } = orderData;
        const receiptContent = `
        <div style="text-align: center;">
          <h2 style="margin-bottom: 10px;">Order Receipt</h2>
          <hr style="border-top: 1px dashed #000; margin: 10px 0;">
          <p style="margin-bottom: 5px;">Order ID: ${reference_id}</p>
          <p style="margin-bottom: 5px;">Date: ${new Date().toLocaleString()}</p>
          <p style="margin-bottom: 5px;">Customer: ${this.state.customer_id ? `${this.state.customer_id}` : "Walk-in Customer"}</p>
          <table style="width: 100%;">
            <thead>
              <tr>
                <th style="text-align: left; padding: 5px;">Product Name</th>
                <th style="text-align: center; padding: 5px;">Quantity</th>
                <th style="text-align: right; padding: 5px;">Price</th>
              </tr>
            </thead>
            <tbody>
              ${this.state.cart
                .map(
                  (c) => `
                    <tr>
                      <td style="text-align: left; padding: 5px;">${c.name}</td>
                      <td style="text-align: center; padding: 5px;">${c.pivot.quantity}</td>
                      <td style="text-align: right; padding: 5px;">${window.APP.currency_symbol} ${(c.price * c.pivot.quantity).toFixed(2)}</td>
                    </tr>
                  `
                )
                .join("")}
            </tbody>
          </table>
          <hr style="border-top: 1px dashed #000; margin: 10px 0;">
          <p style="text-align: right; margin-bottom: 5px;">Gross Total: ${window.APP.currency_symbol} ${this.getTotal(this.state.cart)}</p>
          <p style="text-align: right; margin-bottom: 5px;">Discount Amount: ${window.APP.currency_symbol} - ${discountAmount}</p>
          <p style="text-align: right; margin-bottom: 5px;">Tax Amount: ${window.APP.currency_symbol} ${(this.getTotal(this.state.cart) - discountAmount) * tax}</p>
          <p style="text-align: right; margin-bottom: 5px;">Received Amount: ${receivedAmount}</p>
          <p style="text-align: right; margin-bottom: 5px;">Change: ${orderData.change}</p>
          
        </div>
      `;
      
        const printWindow = window.open("", "_blank");
        printWindow.document.write(`
          <!DOCTYPE html>
          <html>
            <head>
              <title>Receipt</title>
              <style>
                @media print {
        
                    body {
                      font-family: Arial, sans-serif;
                      font-size: 12px;
                    }
              
                    h3 {
                      display: none;
                    }
            
                    @page {
              
                      size: auto;
                      margin-top: 0;
                    }
                }
              </style>
            </head>
            <body>
              ${receiptContent}
            </body>
          </html>
        `);
        printWindow.document.close();
        printWindow.onload = () => {
          printWindow.print();
          printWindow.onafterprint = () => {
            printWindow.close();
          };
        };
      }
      
      
      handleClickSubmit() {
        Swal.fire({
          title: "Received Amount",
          input: "text",
          inputValue: this.state.netTotal.toFixed(2),
          showCancelButton: true,
          confirmButtonText: "Confirm",
          showLoaderOnConfirm: true,
          preConfirm: (amount) => {
            const numericAmount = Number(amount);
            if (isNaN(numericAmount)) {
              Swal.showValidationMessage("The amount must be a number");
              return;
            }
      
            let totalCartValue = this.getTotal(this.state.cart);
            if (this.state.discountAmount > 0) {
              totalCartValue = this.state.netTotal;
            } else {
              totalCartValue = totalCartValue * (1 + window.APP.tax / 100);
            }
      
            const orderReferenceId = this.generateRandomOrderId(8);
            console.log(orderReferenceId);
            return axios
              .post("/admin/orders", {
                customer_id: this.state.customer_id,
                amount: numericAmount,
                discount_id: this.state.discount_id,
                netTotal: this.state.netTotal,
                reference_id: orderReferenceId,
              })
              .then((res) => {
                this.loadCart();
                const change = numericAmount - totalCartValue;
                return {
                  ...res.data,
                  change,
                  receivedAmount: numericAmount,
                  discountAmount: this.state.discountAmount,
                  tax: window.APP.tax / 100,
                  reference_id: orderReferenceId,
                };
              })
              .catch((err) => {
                Swal.showValidationMessage(err.response.data.message);
              });
          },
          allowOutsideClick: () => !Swal.isLoading(),
        }).then((result) => {
          if (result.value) {
            Swal.fire(`Change: ${result.value.change.toFixed(2)}`, " ", "success");
            this.generatePDF(result.value);
          }
        });
      }
      

    render() {
        const {
            cart,
            products,
            customers,
            barcode,
            promoCode,
            discountAmount,
            grossTotal,
            netTotal,
            promoCodes,
        } = this.state;

        return (
            <div className="row">
                <div className="col-md-6 col-lg-7">
                    <div className="mb-2">
                        <input
                            type="text"
                            className="form-control"
                            placeholder="Search Product..."
                            onChange={this.handleChangeSearch}
                            onKeyDown={this.handleSeach}
                        />
                    </div>
                    <div className="order-product">
                        {products.map((p) => (
                            <div
                                onClick={() => this.addProductToCart(p.barcode)}
                                key={p.id}
                                className="item"
                            >
                                <img src={p.image_url} alt="" />
                                <h5
                                    style={
                                        window.APP.warning_quantity > p.quantity
                                            ? { color: "red" }
                                            : {}
                                    }
                                >
                                    {p.name}({p.quantity})
                                </h5>
                            </div>
                        ))}
                    </div>
                </div>
                <div className="col-md-6 col-lg-5">
                    <div className="row mb-2">
                        <div className="col">
                            <form onSubmit={this.handleScanBarcode}>
                                <input
                                    type="text"
                                    className="form-control"
                                    placeholder="Scan Barcode..."
                                    value={barcode}
                                    onChange={this.handleOnChangeBarcode}
                                />
                            </form>
                        </div>
                        <div className="col">
                            <select
                                className="form-control"
                                onChange={this.setCustomerId}
                            >
                                <option value="Walk-In Customer">
                                    Walk-In Customer
                                </option>
                                {customers.map((cus) => (
                                    <option
                                        key={cus.id}
                                        value={cus.id}
                                    >{`${cus.first_name} ${cus.last_name}`}</option>
                                ))}
                            </select>
                        </div>
                    </div>
                    <div className="user-cart">
                        <div className="card">
                            <table className="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Quantity</th>
                                        <th className="text-right">Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {cart.map((c) => (
                                        <tr key={c.id}>
                                            <td>{c.name}</td>
                                            <td>
                                                {c.pivot.quantity > 1 ? (
                                                    <button
                                                        className="btn btn-danger btn-sm"
                                                        onClick={() =>
                                                            this.handleChangeQty(
                                                                c.id,
                                                                c.pivot
                                                                    .quantity -
                                                                    1
                                                            )
                                                        }
                                                    >
                                                        <i className="fas fa-minus"></i>
                                                    </button>
                                                ) : (
                                                    <button
                                                        className="btn btn-danger btn-sm"
                                                        onClick={() =>
                                                            this.handleClickDelete(
                                                                c.id
                                                            )
                                                        }
                                                    >
                                                        <i className="fas fa-trash"></i>
                                                    </button>
                                                )}
                                                <input
                                                    type="text"
                                                    className="form-control form-control-sm qty"
                                                    value={c.pivot.quantity}
                                                    onChange={(event) =>
                                                        this.handleChangeQty(
                                                            c.id,
                                                            event.target.value
                                                        )
                                                    }
                                                />
                                                <button
                                                    className="btn btn-primary btn-sm"
                                                    onClick={() =>
                                                        this.handleChangeQty(
                                                            c.id,
                                                            c.pivot.quantity + 1
                                                        )
                                                    }
                                                >
                                                    <i className="fas fa-plus"></i>
                                                </button>
                                            </td>
                                            <td className="text-right">
                                                {window.APP.currency_symbol}{" "}
                                                {(
                                                    c.price * c.pivot.quantity
                                                ).toFixed(2)}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div className="row">
                        <div className="col">Promo Code:</div>
                        <div className="col">
                            <select
                                className="form-control"
                                value={promoCode}
                                onChange={this.handlePromoCodeChange}
                            >
                                <option value="">Select Promo Code</option>
                                {promoCodes.map((code) => (
                                    <option key={code.id} value={code.code}>
                                        {code.code}
                                    </option>
                                ))}
                            </select>
                        </div>
                    </div>
                    <div className="row">
                        <div className="col">Gross Total:</div>
                        <div className="col text-right">
                            {window.APP.currency_symbol} {this.getTotal(cart)}
                        </div>
                    </div>
                    <div className="row">
                        <div className="col">Discount:</div>
                        <div className="col text-right">
                            {window.APP.currency_symbol} - {discountAmount}
                        </div>
                    </div>
                    <div className="row">
                        <div className="col">Tax:</div>
                        <div className="col text-right">
                            {window.APP.currency_symbol}{" "}
                            {(window.APP.tax / 100) *
                                (this.getTotal(cart) - discountAmount)}
                        </div>
                    </div>
                    <div className="row">
                        <div className="col">Net Total:</div>
                        <div className="col text-right">
                            <strong>
                                {netTotal === 0
                                    ? `${window.APP.currency_symbol} ${(
                                          parseFloat(this.getTotal(cart)) +
                                          parseFloat(this.getTotal(cart)) *
                                              (window.APP.tax / 100)
                                      ).toFixed(2)}`
                                    : `${
                                          window.APP.currency_symbol
                                      } ${parseFloat(netTotal).toFixed(2)}`}
                            </strong>
                        </div>
                    </div>

                    <div className="row">
                        <div className="col">
                            <button
                                type="button"
                                className="btn btn-danger btn-block"
                                onClick={this.handleEmptyCart}
                                disabled={!cart.length}
                            >
                                Cancel
                            </button>
                        </div>
                        <div className="col">
                            <button
                                type="button"
                                className="btn btn-primary btn-block"
                                disabled={!cart.length}
                                onClick={this.handleClickSubmit}
                            >
                                Submit
                            </button>
                        </div>
                    </div>
                    {this.state.receiptData && (
                        <Receipt order={this.state.receiptData} />
                    )}
                </div>
            </div>
        );
    }
}

export default Cart;

if (document.getElementById("cart")) {
    ReactDOM.render(<Cart />, document.getElementById("cart"));
}
