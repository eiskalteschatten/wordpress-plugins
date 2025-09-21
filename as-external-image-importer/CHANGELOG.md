VERSION CHANGELOG:

v1.0.6 Changes:
- Set unlimited execution time (set_time_limit(0))
- Added unreachable domain blacklist for alexseifertmusic.com
- Reduced timeout to 3 seconds (single attempt)
- Reduced per-post time limit to 20 seconds
- Added 512M memory limit

These changes should prevent the 260-post stop by:
1. Avoiding cumulative timeouts on unreachable domains
2. Failing fast on known bad domains
3. Not hitting PHP execution limits
4. Processing all posts quickly by skipping problematic images
