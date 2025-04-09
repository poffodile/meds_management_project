<?php

namespace App\Services\Quotes;

use App\Models\QuoteProduct;

class QuoteProductService
{
    public function saveItems(array $products, int $quoteId): void
    {
        foreach ($products as $productData) {
            $accountCode = $productData['account_code'] === '-No Department-' ? null : $productData['account_code'];

            if($productData['type'] === "add"){
                QuoteProduct::create([
                    'quote_id' => $quoteId,
                    'product_id' => $productData['id'],
                    'product_code' => $productData['product_code'],
                    'title' => $productData['product_name'],
                    'description' => $productData['description'] ?? null,
                    'account_code' => $accountCode,
                    'quantity' => $productData['quantity'],
                    'cost_price' => $productData['cost_price'],
                    'price' => $productData['price'],
                    'markup' => $productData['markup'],
                    'VAT' => $productData['VAT'],
                    'discount' => $productData['discount'],
                    'discount_type' => $productData['discount_type'],

                ]);
            } elseif ($productData['type'] === "edit") {
                QuoteProduct::where('quote_id', $quoteId)
                ->where('product_id', $productData['id'])
                ->update([
                    'product_code' => $productData['product_code'],
                    'title' => $productData['product_name'],
                    'description' => $productData['description'] ?? null,
                    'account_code' => $accountCode,
                    'quantity' => $productData['quantity'],
                    'cost_price' => $productData['cost_price'],
                    'price' => $productData['price'],
                    'markup' => $productData['markup'],
                    'VAT' => $productData['VAT'],
                    'discount' => $productData['discount'],
                    'discount_type' => $productData['discount_type'],
                ]);
            }
        }
    }

    public function getQuoteProductData($id)
    {
        $quoteProduct = QuoteProduct::where('quote_id', $id)->get();
        if (!$quoteProduct) {
            return null; // Handle case when quote is not found
        }

        $products = array();
        foreach ($quoteProduct as $product) {
            $data['id'] = $product->id;
            $data['product_id'] = $product['product_id'];
            $data['product_code'] = $product['product_code'];
            $data['product_name'] = $product['title'] ?? '';
            $data['description'] = $product['description'] ?? '';
            $data['account_code'] = $product['account_code'];
            $data['price'] = $product['price'];
            $data['quantity'] = $product['quantity'];
            $data['cost_price'] = $product['cost_price'];
            $data['price'] = $product['price'];
            $data['markup'] = $product['markup'];
            $data['VAT'] = $product['VAT'];
            $data['discount'] = $product['discount'];
            $data['discount_type'] = $product['discount_type'];
            array_push($products, $data);
        }

        return $products;
    }
}
