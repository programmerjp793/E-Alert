package com.example.ealert.app

import android.content.Intent
import android.os.Bundle
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import com.example.ealert.R
import com.example.ealert.databinding.ActivityVerifyBinding
import com.example.ealert.app.retrofit.RetrofitClient
import kotlinx.coroutines.launch

class VerifyActivity : AppCompatActivity() {

    private lateinit var binding: ActivityVerifyBinding
    private var verificationToken: String? = null

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityVerifyBinding.inflate(layoutInflater)
        setContentView(binding.root)

        setupClickListeners()
    }

    private fun setupClickListeners() {
        binding.btnBack.setOnClickListener { finish() }

        binding.imgbtnContinue.setOnClickListener {
            val email = binding.etEnterEmail.text.toString().trim()
            if (email.isNotEmpty()) {
                verifyEmail(email)
            } else {
                Toast.makeText(this, "Please enter your email", Toast.LENGTH_SHORT).show()
            }
        }

        binding.tvBackToLogin.setOnClickListener {
            startActivity(Intent(this, LoginActivity::class.java))
            finish()
        }
    }

    private fun verifyEmail(email: String) {
        lifecycleScope.launch {
            try {
                val response = RetrofitClient.instance.verifyEmail(email)
                if (response.isSuccessful && response.body()?.status == "success") {
                    verificationToken = response.body()?.token

                    // Proceed to password reset
                    val intent = Intent(this@VerifyActivity, ChangePasswordActivity::class.java).apply {
                        putExtra("email", email)
                        putExtra("verification_token", verificationToken)
                    }
                    startActivity(intent)
                    finish()
                } else {
                    Toast.makeText(
                        this@VerifyActivity,
                        response.body()?.message ?: "Email not found",
                        Toast.LENGTH_SHORT
                    ).show()
                }
            } catch (e: Exception) {
                Toast.makeText(
                    this@VerifyActivity,
                    "Error: ${e.message}",
                    Toast.LENGTH_SHORT
                ).show()
            }
        }
    }
}