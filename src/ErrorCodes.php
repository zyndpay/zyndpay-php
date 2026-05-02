<?php

declare(strict_types=1);

namespace ZyndPay;

/**
 * Machine-readable error codes returned by the ZyndPay API.
 *
 * Mirrors packages/types/src/error-codes.ts. A sync test in
 * tests/ErrorCodesSyncTest.php fails CI if this drifts.
 */
final class ErrorCodes
{
    // ─── Auth ──────────────────────────────────────────────────────────────
    public const UNAUTHORIZED = 'UNAUTHORIZED';
    public const FORBIDDEN = 'FORBIDDEN';
    public const TOTP_REQUIRED = 'TOTP_REQUIRED';
    public const TOTP_INVALID = 'TOTP_INVALID';
    public const BACKUP_CODE_INVALID = 'BACKUP_CODE_INVALID';
    public const EMAIL_UNVERIFIED = 'EMAIL_UNVERIFIED';
    public const ACCOUNT_CLOSED = 'ACCOUNT_CLOSED';
    public const INSUFFICIENT_SCOPE = 'INSUFFICIENT_SCOPE';
    public const INVALID_API_KEY = 'INVALID_API_KEY';
    public const REGISTRATION_BLOCKED = 'REGISTRATION_BLOCKED';
    public const SANDBOX_KEY_LIVE_REQUEST = 'SANDBOX_KEY_LIVE_REQUEST';
    public const LIVE_KEY_SANDBOX_REQUEST = 'LIVE_KEY_SANDBOX_REQUEST';
    public const IP_ALLOWLIST_SELF_LOCKOUT = 'IP_ALLOWLIST_SELF_LOCKOUT';

    // ─── Merchant / KYB ────────────────────────────────────────────────────
    public const MERCHANT_NOT_FOUND = 'MERCHANT_NOT_FOUND';
    public const MERCHANT_NOT_LIVE = 'MERCHANT_NOT_LIVE';
    public const MERCHANT_STATUS_INVALID = 'MERCHANT_STATUS_INVALID';
    public const KYB_REQUIRED = 'KYB_REQUIRED';

    // ─── Limits ────────────────────────────────────────────────────────────
    public const RATE_LIMIT_EXCEEDED = 'RATE_LIMIT_EXCEEDED';
    public const RATE_LIMITED = 'RATE_LIMITED';
    public const MERCHANT_LIMIT_EXCEEDED = 'MERCHANT_LIMIT_EXCEEDED';
    public const MONTHLY_LIMIT_EXCEEDED = 'MONTHLY_LIMIT_EXCEEDED';
    public const BALANCE_CAP_REACHED = 'BALANCE_CAP_REACHED';
    public const DAILY_CAP_EXCEEDED = 'DAILY_CAP_EXCEEDED';
    public const DAILY_CONVERSION_LIMIT = 'DAILY_CONVERSION_LIMIT';
    public const DAILY_CONVERSION_COUNT_LIMIT = 'DAILY_CONVERSION_COUNT_LIMIT';
    public const LIMIT_EXCEEDED_PAYLINKS = 'LIMIT_EXCEEDED_PAYLINKS';
    public const LIMIT_EXCEEDED_WEBHOOKS = 'LIMIT_EXCEEDED_WEBHOOKS';
    public const LIMIT_EXCEEDED_API_KEYS = 'LIMIT_EXCEEDED_API_KEYS';
    public const LIMIT_EXCEEDED_TEAM_MEMBERS = 'LIMIT_EXCEEDED_TEAM_MEMBERS';
    public const LIMIT_EXCEEDED_BULK_BATCH = 'LIMIT_EXCEEDED_BULK_BATCH';

    // ─── Multi-wallet / fiat destinations ────────────────────────────────
    public const WALLET_NOT_FOUND = 'WALLET_NOT_FOUND';
    public const WALLET_DIRECT_WITHDRAW_DISABLED = 'WALLET_DIRECT_WITHDRAW_DISABLED';
    public const FIAT_DESTINATION_REQUIRED = 'FIAT_DESTINATION_REQUIRED';
    public const FIAT_DESTINATION_NOT_FOUND = 'FIAT_DESTINATION_NOT_FOUND';
    public const FIAT_DESTINATION_INVALID = 'FIAT_DESTINATION_INVALID';
    public const INVALID_CONVERSION_PAIR = 'INVALID_CONVERSION_PAIR';
    public const REFUND_RAIL_NOT_AVAILABLE = 'REFUND_RAIL_NOT_AVAILABLE';
    public const REFUND_ORIGIN_WALLET_UNKNOWN = 'REFUND_ORIGIN_WALLET_UNKNOWN';
    public const WITHDRAWAL_NOT_FIAT = 'WITHDRAWAL_NOT_FIAT';
    public const WITHDRAWAL_NOT_PENDING_REVIEW = 'WITHDRAWAL_NOT_PENDING_REVIEW';

    // ─── Address / wallet ─────────────────────────────────────────────────
    public const INVALID_ADDRESS = 'INVALID_ADDRESS';
    public const ADDRESS_IN_USE = 'ADDRESS_IN_USE';
    public const ADDRESS_NOT_FOUND = 'ADDRESS_NOT_FOUND';
    public const NO_WHITELISTED_ADDRESS = 'NO_WHITELISTED_ADDRESS';
    public const NO_PAYOUT_ADDRESS = 'NO_PAYOUT_ADDRESS';
    public const ADDRESS_NOT_WHITELISTED = 'ADDRESS_NOT_WHITELISTED';
    public const ADDRESS_COOLDOWN = 'ADDRESS_COOLDOWN';
    public const WHITELIST_VALIDATION_FAILED = 'WHITELIST_VALIDATION_FAILED';
    public const INVALID_CONTEXT = 'INVALID_CONTEXT';
    public const INVALID_CONTEXTS = 'INVALID_CONTEXTS';

    // ─── Transaction ──────────────────────────────────────────────────────
    public const INSUFFICIENT_BALANCE = 'INSUFFICIENT_BALANCE';
    public const AMOUNT_TOO_SMALL = 'AMOUNT_TOO_SMALL';
    public const AMOUNT_TOO_LARGE = 'AMOUNT_TOO_LARGE';
    public const INVALID_AMOUNT = 'INVALID_AMOUNT';
    public const INVALID_TRANSACTION_TYPE = 'INVALID_TRANSACTION_TYPE';
    public const INVALID_TRANSACTION_STATUS = 'INVALID_TRANSACTION_STATUS';
    public const REFUND_WINDOW_EXPIRED = 'REFUND_WINDOW_EXPIRED';
    public const REFUND_EXCEEDS_AMOUNT = 'REFUND_EXCEEDS_AMOUNT';
    public const REASON_NOTE_REQUIRED = 'REASON_NOTE_REQUIRED';
    public const CANNOT_CANCEL = 'CANNOT_CANCEL';
    public const DUPLICATE_EXTERNAL_REF = 'DUPLICATE_EXTERNAL_REF';
    public const MISSING_IDEMPOTENCY_KEY = 'MISSING_IDEMPOTENCY_KEY';
    public const IDEMPOTENCY_KEY_INVALID = 'IDEMPOTENCY_KEY_INVALID';
    public const IDEMPOTENCY_KEY_MISMATCH = 'IDEMPOTENCY_KEY_MISMATCH';
    public const CONFIG_MISSING = 'CONFIG_MISSING';

    // ─── AML / compliance ─────────────────────────────────────────────────
    public const AML_BLOCKED = 'AML_BLOCKED';
    public const AML_SCREENING_UNAVAILABLE = 'AML_SCREENING_UNAVAILABLE';

    // ─── Conversion / FX ──────────────────────────────────────────────────
    public const CONVERSION_NOT_ALLOWED = 'CONVERSION_NOT_ALLOWED';
    public const RATE_LOCK_EXPIRED = 'RATE_LOCK_EXPIRED';
    public const RATE_LOCK_NOT_FOUND = 'RATE_LOCK_NOT_FOUND';
    public const RATE_LOCK_ALREADY_USED = 'RATE_LOCK_ALREADY_USED';
    public const RATE_UNAVAILABLE = 'RATE_UNAVAILABLE';
    public const NEGATIVE_REVENUE = 'NEGATIVE_REVENUE';
    public const RATE_STALE = 'RATE_STALE';
    public const MISSING_PHONE = 'MISSING_PHONE';
    public const MISSING_OPERATOR = 'MISSING_OPERATOR';
    public const MISSING_BANK_DETAILS = 'MISSING_BANK_DETAILS';
    public const MISSING_MOBILE_MONEY_FIELDS = 'MISSING_MOBILE_MONEY_FIELDS';
    public const MISSING_BANK_FIELDS = 'MISSING_BANK_FIELDS';

    // ─── Beneficiary ──────────────────────────────────────────────────────
    public const BENEFICIARY_REQUIRED = 'BENEFICIARY_REQUIRED';
    public const BENEFICIARY_INVALID = 'BENEFICIARY_INVALID';
    public const BENEFICIARY_NOT_FOUND = 'BENEFICIARY_NOT_FOUND';
    public const BENEFICIARY_ALREADY_EXISTS = 'BENEFICIARY_ALREADY_EXISTS';
    public const BENEFICIARY_IN_USE = 'BENEFICIARY_IN_USE';
    public const BENEFICIARY_REJECTED = 'BENEFICIARY_REJECTED';
    public const BENEFICIARY_COOLDOWN = 'BENEFICIARY_COOLDOWN';
    public const BENEFICIARY_NOT_VERIFIED = 'BENEFICIARY_NOT_VERIFIED';
    public const SELF_OWNED_REQUIRED = 'SELF_OWNED_REQUIRED';
    public const MAX_BENEFICIARIES_REACHED = 'MAX_BENEFICIARIES_REACHED';
    public const THIRD_PARTY_NOT_ALLOWED = 'THIRD_PARTY_NOT_ALLOWED';

    // ─── Invoice ──────────────────────────────────────────────────────────
    public const INVOICE_IMMUTABLE = 'INVOICE_IMMUTABLE';
    public const INVALID_STATUS = 'INVALID_STATUS';
    public const NOT_ISSUED = 'NOT_ISSUED';
    public const NO_RECIPIENT = 'NO_RECIPIENT';
    public const NO_BILLABLE_ITEMS = 'NO_BILLABLE_ITEMS';

    // ─── Bulk payment ─────────────────────────────────────────────────────
    public const IMPORT_VALIDATION_FAILED = 'IMPORT_VALIDATION_FAILED';

    // ─── Admin / reconciliation ───────────────────────────────────────────
    public const NO_DISCREPANCY = 'NO_DISCREPANCY';

    // ─── Card payments (#372) ─────────────────────────────────────────────
    public const CARD_PAYMENTS_DISABLED = 'CARD_PAYMENTS_DISABLED';
    public const FEE_NOT_CONFIGURED = 'FEE_NOT_CONFIGURED';
    public const MOBILE_MONEY_PAYMENTS_DISABLED = 'MOBILE_MONEY_PAYMENTS_DISABLED';
    public const USDT_PAYMENTS_DISABLED = 'USDT_PAYMENTS_DISABLED';
    public const NO_METHODS_ENABLED = 'NO_METHODS_ENABLED';
    public const CANNOT_CHANGE_CURRENCY_AFTER_PRODUCTS = 'CANNOT_CHANGE_CURRENCY_AFTER_PRODUCTS';
    public const PAYLINK_EMPTY = 'PAYLINK_EMPTY';
    public const PAYMENT_METHOD_NOT_ACCEPTED = 'PAYMENT_METHOD_NOT_ACCEPTED';
    public const PROVIDER_INITIATE_FAILED = 'PROVIDER_INITIATE_FAILED';
    public const PROVIDER_MISSING_URL = 'PROVIDER_MISSING_URL';
    public const FX_UNAVAILABLE = 'FX_UNAVAILABLE';

    // ─── Paylink eligibility engine ───────────────────────────────────────
    public const PAYLINK_RAIL_DISABLED = 'PAYLINK_RAIL_DISABLED';
    public const PAYLINK_CURRENCY_UNSUPPORTED = 'PAYLINK_CURRENCY_UNSUPPORTED';
    public const PAYLINK_AMOUNT_BELOW_MINIMUM = 'PAYLINK_AMOUNT_BELOW_MINIMUM';
    public const PAYLINK_AMOUNT_ABOVE_MAXIMUM = 'PAYLINK_AMOUNT_ABOVE_MAXIMUM';
    public const PAYLINK_METHOD_TEMPORARILY_DOWN = 'PAYLINK_METHOD_TEMPORARILY_DOWN';
    public const PAYLINK_MERCHANT_FEE_INVIABLE = 'PAYLINK_MERCHANT_FEE_INVIABLE';
    public const PAYLINK_CONVERSION_UNAVAILABLE = 'PAYLINK_CONVERSION_UNAVAILABLE';

    // ─── MoMo payin sans redirection ──────────────────────────────────────
    public const OPERATOR_NOT_SUPPORTED = 'OPERATOR_NOT_SUPPORTED';
    public const INVALID_STATE = 'INVALID_STATE';
    public const OTP_INVALID = 'OTP_INVALID';
    public const OTP_EXPIRED = 'OTP_EXPIRED';
    public const OTP_NOT_APPLICABLE = 'OTP_NOT_APPLICABLE';
    public const MOMO_PROVIDER_ERROR = 'MOMO_PROVIDER_ERROR';
    public const CUSTOMER_PHONE_REQUIRED = 'CUSTOMER_PHONE_REQUIRED';

    // ─── Marketplace / Connect (#223) ─────────────────────────────────────
    public const MARKETPLACE_DISABLED = 'MARKETPLACE_DISABLED';
    public const NOT_A_PLATFORM_MERCHANT = 'NOT_A_PLATFORM_MERCHANT';
    public const SPLIT_RULE_NOT_FOUND = 'SPLIT_RULE_NOT_FOUND';
    public const SPLIT_RULE_IN_USE = 'SPLIT_RULE_IN_USE';
    public const SPLIT_RULE_INVALID_BPS_SUM = 'SPLIT_RULE_INVALID_BPS_SUM';
    public const SPLIT_RULE_MISSING_ZYNDPAY_RECIPIENT = 'SPLIT_RULE_MISSING_ZYNDPAY_RECIPIENT';
    public const SPLIT_RULE_MISSING_PLATFORM_RECIPIENT = 'SPLIT_RULE_MISSING_PLATFORM_RECIPIENT';
    public const SPLIT_RULE_MISSING_SUB_MERCHANT_RECIPIENT = 'SPLIT_RULE_MISSING_SUB_MERCHANT_RECIPIENT';
    public const SPLIT_RULE_INVALID_ZYNDPAY_BPS = 'SPLIT_RULE_INVALID_ZYNDPAY_BPS';
    public const SUB_MERCHANT_NOT_CONNECTED = 'SUB_MERCHANT_NOT_CONNECTED';
    public const SUB_MERCHANT_ALREADY_CONNECTED = 'SUB_MERCHANT_ALREADY_CONNECTED';
    public const SPLIT_PAYMENT_NOT_FOUND = 'SPLIT_PAYMENT_NOT_FOUND';
    public const SPLIT_PAYMENT_ALREADY_REVERSED = 'SPLIT_PAYMENT_ALREADY_REVERSED';

    // ─── Marketplace / Connect Phase 2 (#224) ─────────────────────────────
    public const SUB_MERCHANT_KYB_REQUIRED = 'SUB_MERCHANT_KYB_REQUIRED';
    public const SUB_MERCHANT_SUSPENDED = 'SUB_MERCHANT_SUSPENDED';
    public const SUB_MERCHANT_AML_FLAGGED = 'SUB_MERCHANT_AML_FLAGGED';
    public const SUB_MERCHANT_INVITATION_NOT_FOUND = 'SUB_MERCHANT_INVITATION_NOT_FOUND';
    public const SUB_MERCHANT_INVITATION_EXPIRED = 'SUB_MERCHANT_INVITATION_EXPIRED';
    public const SUB_MERCHANT_INVITATION_ALREADY_USED = 'SUB_MERCHANT_INVITATION_ALREADY_USED';
    public const SUB_MERCHANT_HAS_PENDING_BALANCE = 'SUB_MERCHANT_HAS_PENDING_BALANCE';

    // ─── Generic ──────────────────────────────────────────────────────────
    public const VALIDATION_ERROR = 'VALIDATION_ERROR';
    public const DUPLICATE_RESOURCE = 'DUPLICATE_RESOURCE';
    public const NOT_FOUND = 'NOT_FOUND';
    public const CONFLICT = 'CONFLICT';
    public const BAD_REQUEST = 'BAD_REQUEST';
    public const INTERNAL_ERROR = 'INTERNAL_ERROR';
    public const SERVICE_UNAVAILABLE = 'SERVICE_UNAVAILABLE';

    private function __construct()
    {
    }
}
