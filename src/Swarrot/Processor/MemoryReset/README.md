# MemoryResetProcessor

MemoryResetProcessor is a [swarrot](https://github.com/swarrot/swarrot) processor.
The goal of this processor is to reset the peak memory usage after processing each message, to allow profiling tools
distinguishing each message to report an accurate peak memory usage.
As resetting the peak memory usage is supported only on PHP 8.2+, this processor is a no-op on older PHP versions.
